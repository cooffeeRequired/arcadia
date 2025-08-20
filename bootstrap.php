<?php
/** @noinspection ALL */
declare(strict_types=1);

error_reporting(E_ALL);

use Core\Authorization\Session;
use Core\Cache\CacheManager;
use Core\Database\DatabaseLogger;
use Core\Facades\Container;
use Core\Helpers\TranslationHelper;
use Core\Logging\Tracy;
use Core\Middleware\ModuleMiddleware;
use Core\Middleware\ToastMiddleware;
use Core\Providers\ModuleServiceProvider;
use Core\Render\Renderer;
use Core\Render\View;
use Core\State\RedisContainer;
use Dba\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMSetup;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

const APP_ROOT = __DIR__;

// Default ENV
if (!getenv('APP_ENV')) {
    putenv('APP_ENV=development');
}

final class Bootstrap
{
    public static function autoload(string $path = '/vendor/autoload.php'): void
    {
        require_once __DIR__ . $path;
        require_once __DIR__ . '/core/helpers.php';
        require_once __DIR__ . '/core/helpers/toast.php';
        /** @var array<string,mixed> $cfg */
        $cfg = require __DIR__ . '/config/config.php';
        define('APP_CONFIGURATION', $cfg);
        Container::set('config', APP_CONFIGURATION);
    }

    /**
     * @return array{0:Predis\Client,1:RedisAdapter,2:Session}
     */
    public static function init(): array
    {
        Tracy::init();

        $redis = new Predis\Client('tcp://127.0.0.1:6379', [
            'cluster'     => 'redis',
            'parameters'  => ['database' => 2],
        ]);

        Session::init();
        $session = new Session($redis, null, 'arcadia');

        $cache = new RedisAdapter($redis);

        Container::set('redis', $redis);
        Container::set('cache', $cache);
        Container::set('session', $session);
        RedisContainer::init($redis);

        return [$redis, $cache, $session];
    }

    public static function tryMakeConnection(CacheItemPoolInterface $cache): void
    {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [APP_CONFIGURATION['entities_dir']],
            isDevMode: (getenv('APP_ENV') === 'development'),
            proxyDir: APP_CONFIGURATION['cache_dir'],
            cache: $cache,
        );

        $config->setSQLLogger(new DatabaseLogger());
        $config->setMetadataCache($cache);
        $config->setQueryCache($cache);
        $config->setResultCache($cache);

        try {
            /** @var array<string,mixed> $dbParams */
            $dbParams   = require __DIR__ . '/config/doctrine.php';
            $connection = DriverManager::getConnection($dbParams, $config);
            $em         = new EntityManager($connection, $config);

            // Základní služby
            $renderer = new Renderer();
            Container::set(Connection::class, $connection, ['doctrine.connection']);
            Container::set(EntityManager::class, $em, ['doctrine.em']);
            Container::set(Renderer::class, $renderer, ['app.renderer']);

            CacheManager::init();
            View::init();
            ToastMiddleware::autoHandle();
            TranslationHelper::init();

            // Načtení modulů při bootu aplikace
            ModuleServiceProvider::boot();

            // Spuštění middleware pro moduly
            ModuleMiddleware::handle();

        } catch (ORMException|\Doctrine\DBAL\Exception $e) {
            $html = View::render('errors.generic', [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'title'   => 'Nastala chyba',
            ]);
            echo $html;
        }
    }
}

function boot(): void
{
    Bootstrap::autoload();
    [, $cache] = Bootstrap::init();
    Bootstrap::tryMakeConnection($cache);
    require __DIR__ . '/app/routes.php';
}
