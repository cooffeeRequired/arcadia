<?php

error_reporting(E_ALL);

// Nastavení environment
if (!getenv('APP_ENV')) {
    putenv('APP_ENV=development');
}

use Core\Authorization\Session;
use Core\Database\DatabaseLogger;
use Core\Facades\Container;
use Core\Logging\Tracy;
use Core\Middleware\ToastMiddleware;
use Core\Render\Renderer;
use Core\Render\View;

use Core\State\RedisContainer;
use Dba\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\RedisAdapter;

const APP_ROOT = __DIR__;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/helpers.php';
require_once __DIR__ . '/core/helpers/toast.php';
define("APP_CONFIGURATION", require_once __DIR__ . '/config/config.php');

Tracy::init();

$redis = new Predis\Client('tcp://127.0.0.1:6379', [
    'cluster' => 'redis',
    'parameters' => [
        'database' => 2  // Použij cluster 2
    ]
]);

// Inicializace klasických souborových session
Session::init();

// Vytvoř Session instanci s Redis podporou
$session = new Session($redis, null, 'arcadia');
$cache = new RedisAdapter($redis);

// Registrace cache služeb do containeru
Container::set('redis', $redis);
Container::set('cache', $cache);
Container::set('session', $session);

RedisContainer::init($redis);

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [APP_ROOT . "/app/Entities"],
    isDevMode: true, // Dev mode pro debugging, ale cache je explicitně nastaven
    proxyDir: APP_CONFIGURATION['cache_dir'],
    cache: $cache,
);

// Přidání SQL loggeru pro Tracy
$config->setSQLLogger(new DatabaseLogger());
$config->setMetadataCache($cache);
$config->setQueryCache($cache);
$config->setResultCache($cache);

try {
    $conn = require __DIR__ . '/config/doctrine.php';
    $connection = DriverManager::getConnection($conn, $config);
    $em = new EntityManager($connection, $config);
    /**
     * @var PDO $pdo
     */
    $pdo = $em->getConnection()->getNativeConnection();

    $renderer = new Renderer();
    Container::set(Connection::class, $connection, ['doctrine.connection']);
    Container::set(EntityManager::class, $em, ['doctrine.em']);
    Container::set(Renderer::class, $renderer, ['app.renderer']);

    View::init();
    ToastMiddleware::autoHandle();

} catch (ORMException|\Doctrine\DBAL\Exception $e) {
    $html = View::render('errors.generic', [
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'title' => 'Nestala chyba'
    ]);
    echo $html;
}

function boot(): void
{
    require_once __DIR__ . '/app/router.php';

}
