<?php

namespace App\Controllers;

use Core\Facades\Container;
use Core\Render\View;
use Doctrine\ORM\EntityManager;

class SettingsController
{
    private EntityManager $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function index()
    {
        // Zde by byly načteny nastavení z databáze nebo konfiguračních souborů
        $settings = [
            'company_name' => 'Arcadia CRM',
            'company_email' => 'info@arcadia.cz',
            'company_phone' => '+420 123 456 789',
            'company_address' => 'Václavská 1, Praha 1',
            'default_currency' => 'CZK',
            'date_format' => 'd.m.Y',
            'time_format' => 'H:i',
            'timezone' => 'Europe/Prague',
            'pagination_per_page' => 20,
            'auto_refresh_enabled' => true,
            'notifications_enabled' => true
        ];

        return View::render('settings.index', [
            'settings' => $settings
        ]);
    }

    public function update()
    {
        // Zde by byla logika pro uložení nastavení
        // Pro demonstraci jen přesměrujeme zpět
        
        header('Location: /settings');
        exit;
    }

    public function profile()
    {
        // Zde by byly načteny údaje o aktuálním uživateli
        $user = [
            'name' => 'Admin User',
            'email' => 'admin@arcadia.cz',
            'role' => 'Administrator',
            'last_login' => new \DateTime(),
            'created_at' => new \DateTime('2024-01-01')
        ];

        return View::render('settings.profile', [
            'user' => $user
        ]);
    }

    public function updateProfile()
    {
        // Zde by byla logika pro aktualizaci profilu
        // Pro demonstraci jen přesměrujeme zpět
        
        header('Location: /settings/profile');
        exit;
    }

    public function system()
    {
        // Získání informací o OPcache
        $opcacheStatus = get_opcache_status();
        $opcacheEnabled = function_exists('opcache_get_status');
        
        // Systémové informace
        $systemInfo = [
            'php_version' => PHP_VERSION,
            'doctrine_version' => \Doctrine\ORM\Version::VERSION,
            'database_driver' => 'MySQL/PostgreSQL',
            'cache_driver' => 'Redis',
            'environment' => 'Development',
            'debug_mode' => true,
            'last_backup' => new \DateTime('2024-01-15'),
            'disk_usage' => '2.5 GB / 10 GB',
            'memory_usage' => '128 MB',
            'opcache_enabled' => $opcacheEnabled,
            'opcache_status' => $opcacheStatus
        ];

        return View::render('settings.system', [
            'systemInfo' => $systemInfo
        ]);
    }

    public function clearCache()
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $success = clear_cache();
        
        if ($success) {
            $_SESSION['flash_message'] = 'Cache byla úspěšně vymazána.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Chyba při vymazání cache.';
            $_SESSION['flash_type'] = 'error';
        }
        
        header('Location: /settings/system');
        exit;
    }

    public function optimizeOpCache()
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $success = optimize_opcache();
        
        if ($success) {
            $_SESSION['flash_message'] = 'OPcache byl úspěšně optimalizován.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Chyba při optimalizaci OPcache.';
            $_SESSION['flash_type'] = 'error';
        }
        
        header('Location: /settings/system');
        exit;
    }

    public function createBackup()
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $success = create_backup();
        
        if ($success) {
            $_SESSION['flash_message'] = 'Záloha byla úspěšně vytvořena.';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Chyba při vytváření zálohy.';
            $_SESSION['flash_type'] = 'error';
        }
        
        header('Location: /settings/system');
        exit;
    }

    public function systemLogs()
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $logs = get_system_logs();
        
        return View::render('settings.logs', [
            'logs' => $logs
        ]);
    }

    public function checkIntegrity()
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!isset($_SESSION['user_id'])) {
            header('Location: /login');
            exit;
        }

        $integrityCheck = check_database_integrity();
        
        if ($integrityCheck['success']) {
            $_SESSION['flash_message'] = 'Kontrola integrity proběhla úspěšně. ' . $integrityCheck['message'];
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = 'Problémy s integritou databáze: ' . $integrityCheck['message'];
            $_SESSION['flash_type'] = 'error';
        }
        
        header('Location: /settings/system');
        exit;
    }
} 