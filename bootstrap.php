<?php
declare(strict_types=1);

error_reporting(E_ALL);

use Core\Authorization\Session;
use Core\Cache\CacheManager;
use Core\Database\DatabaseLogger;
use Core\Facades\Container;
use Core\Logging\Tracy;
use Core\Middleware\ToastMiddleware;
use Core\Modules\ModuleManager; // nová fasáda
use Core\Modules\Services\DependencyChecker;
use Core\Modules\Services\MigrationService;
use Core\Modules\Services\ModuleCatalog;
use Core\Modules\Services\ModuleService;
use Core\Modules\Infra\DoctrineModuleRepository;
use Core\Modules\Infra\FilesystemScanner;
use Core\Modules\Infra\PhpConfigLoader;
use Core\Modules\Contracts\MigrationRunnerInterface;
use Core\Modules\DTO\MigrationInfo;
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

            // ---- Module stack (nové rozdělení) ----
            $appCfg     = Container::get('config');
            $cfgLoader  = new PhpConfigLoader($appCfg);
            $repo       = new DoctrineModuleRepository($em);
            $fsScanner  = new FilesystemScanner($cfgLoader);
            $deps       = new DependencyChecker($repo, $cfgLoader);

            // Minimální runner přes require + konvenci migrate()/rollback()
            $runner = new class($em) implements MigrationRunnerInterface {
                public function __construct(private readonly EntityManager $em) {}

                public function run(string $moduleName, MigrationInfo $m): void
                {
                    require_once $m->filePath;
                    $class = $this->extractClass($m->phpContent)
                        ?? throw new RuntimeException("Nenalezena třída v {$m->file}");
                    $obj = new $class();
                    if (method_exists($obj, 'migrate')) {
                        $obj->migrate();
                    }
                }

                public function rollback(string $moduleName, MigrationInfo $m): void
                {
                    require_once $m->filePath;
                    $class = $this->extractClass($m->phpContent)
                        ?? throw new RuntimeException("Nenalezena třída v {$m->file}");
                    $obj = new $class();
                    if (method_exists($obj, 'rollback')) {
                        $obj->rollback();
                    }
                }

                public function isExecuted(string $version): bool
                {
                    $conn = $this->em->getConnection();
                    /** @var int|string $cnt */
                    $cnt = $conn->fetchOne('SELECT COUNT(*) FROM migrations WHERE version = ?', [$version]);
                    return (int)$cnt > 0;
                }

                public function markExecuted(string $version, string $description): void
                {
                    $this->em->getConnection()->executeStatement(
                        'INSERT INTO migrations(version, description, executed_at) VALUES (?,?,NOW())',
                        [$version, $description]
                    );
                }

                public function markNotExecuted(string $version): void
                {
                    $this->em->getConnection()->executeStatement(
                        'DELETE FROM migrations WHERE version = ?',
                        [$version]
                    );
                }

                private function extractClass(string $php): ?string
                {
                    return preg_match('/class\s+(\w+)/', $php, $m) ? $m[1] : null;
                }
            };

            $moduleService  = new ModuleService($repo, $deps, $cfgLoader);
            $moduleCatalog  = new ModuleCatalog($repo, $fsScanner, $cfgLoader);
            $migrationSvc   = new MigrationService($fsScanner, $runner);
            $moduleManager  = new ModuleManager($moduleService, $moduleCatalog, $migrationSvc);

            Container::set(ModuleManager::class, $moduleManager, ['app.module_manager']);
            // ---------------------------------------

            CacheManager::init();
            View::init();
            ToastMiddleware::autoHandle();

        } catch (ORMException|\Doctrine\DBAL\Exception $e) {
            $html = View::render('errors.generic', [
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'title'   => 'Nastala chyba',
            ]);
            echo $html;
        }
    }

    public static function tryLoadModules(): void
    {
        /** @var ModuleManager $modules */
        $modules = Container::get(ModuleManager::class, ModuleManager::class);

        $scan = $modules->scanAndSync();
        debug_log('Arcadia CRM: scan ' . json_encode($scan));

        if (!empty($scan['new_modules'])) {
            $insert = $modules->autoInsertNew();
            debug_log('Arcadia CRM: inserted ' . json_encode($insert));
        }

        // 3) volitelně: pro vybrané moduly spustit migrace
        // foreach (['Sales','Inventory'] as $m) { $modules->runModuleMigrations($m); }

        // 4) případně status migrací
        // $status = $modules->getModuleMigrationStatus('Sales');
        // debug_log('Sales migrations status: ' . json_encode($status));
    }
}

function boot(): void
{
    Bootstrap::autoload();
    [, $cache] = Bootstrap::init();
    Bootstrap::tryMakeConnection($cache);
    Bootstrap::tryLoadModules();
    require __DIR__ . '/app/router.php';
}
