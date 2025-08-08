<?php

use Core\Database\DatabaseLogger;
use Core\Facades\Container;
use Core\Logging\Tracy;
use Core\Notification\Notification;
use Core\Render\Renderer;
use Core\Render\View;
use Core\State\DBSessionHandler;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\ORMSetup;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

const APP_ROOT = __DIR__;

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/helpers.php';
define("APP_CONFIGURATION", require_once __DIR__ . '/config/config.php');

// Inicializace Symfony Var Dumper pro lepší výstup
if (class_exists('Symfony\Component\VarDumper\VarDumper')) {
    VarDumper::setHandler(function ($var) {
        $cloner = new VarCloner();
        $dumper = new HtmlDumper();
        $dumper->setTheme('light');
        $dumper->dump($cloner->cloneVar($var));
    });
}

Tracy::init();

$redis = new Predis\Client('tcp://127.0.0.1:6379');
$cache = new RedisAdapter($redis);

// Registrace cache služeb do containeru
Container::set('redis', $redis);
Container::set('cache', $cache);

$config = ORMSetup::createAttributeMetadataConfiguration(
    paths: [APP_ROOT . "/app/Entities"],
    isDevMode: true, // Dev mode pro debugging, ale cache je explicitně nastaven
    proxyDir: APP_CONFIGURATION['cache_dir'],
    cache: $cache,
);

// Přidání SQL loggeru pro Tracy
$config->setSQLLogger(new DatabaseLogger());

// Explicitní nastavení cache driverů
$config->setMetadataCache($cache);
$config->setQueryCache($cache);
$config->setResultCache($cache);

// Cache lifetime se nastavuje při vytváření query nebo result cache

try {
    $conn = require __DIR__ . '/config/doctrine.php';
    $connection = DriverManager::getConnection($conn, $config);
    $em = new EntityManager($connection, $config);
    /**
     * @var PDO $pdo
     */
    $pdo = $em->getConnection()->getNativeConnection();
    $handler = new DBSessionHandler($pdo);
    session_set_save_handler($handler, true);
    session_start();

    Container::set(EntityManager::class, $em, ['doctrine.em']);

    $renderer = new Renderer();
    $renderer->handle(); // Inicializace s Request objektem

    Container::set(Renderer::class, $renderer, ['app.renderer']);

    // Registrace View Factory pro Laravel komponenty
    View::init();
    Notification::init();

} catch (ORMException|\Doctrine\DBAL\Exception $e) {
    echo $e->getMessage();
}

function boot(): void
{
    require_once __DIR__ . '/app/router.php';
}