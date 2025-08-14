<?php

namespace App\Controllers;

use Core\Cache\CacheManager;
use Core\Facades\Container;
use Core\Http\Response;
use Core\Render\BaseController;
use Core\State\RedisContainer;

class SettingsController extends BaseController
{
    public function index(): Response\ViewResponse
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

        return $this->view('settings.index', [
            'settings' => $settings
        ]);
    }

    public function update(): void
    {
        // Zde by byla logika pro uložení nastavení
        // Pro demonstraci jen přesměrujeme zpět

        $this->redirect('/settings');
    }

    public function profile(): Response\ViewResponse
    {
        // Zde by byly načteny údaje o aktuálním uživateli
        $user = [
            'name' => 'Admin User',
            'email' => 'admin@arcadia.cz',
            'role' => 'Administrator',
            'last_login' => new \DateTime(),
            'created_at' => new \DateTime('2024-01-01')
        ];

        return $this->view('settings.profile', [
            'user' => $user
        ]);
    }

    public function updateProfile(): void
    {
        // Zde by byla logika pro aktualizaci profilu
        // Pro demonstraci jen přesměrujeme zpět

        $this->redirect('/settings/profile');
    }

    public function system(): Response\ViewResponse
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

        return $this->view('settings.system', [
            'systemInfo' => $systemInfo
        ]);
    }

    public function clearCache(): void
    {
        if (!$this->session('user')) {
            $this->redirect('/login');
        }

        $cacheResults = CacheManager::clearAllCache();

        // Kontrola výsledků
        $allSuccess = true;
        $failedTypes = [];

        foreach ($cacheResults as $type => $success) {
            if (!$success) {
                $allSuccess = false;
                $failedTypes[] = $type;
            }
        }
        if ($allSuccess) {
            $this->toastSuccess('Všechna cache byla úspěšně vymazána (soubory + Redis + ORM + OPcache + session)');
        } else {
            $failedTypesStr = implode(', ', $failedTypes);
            $this->toastWarning("Cache byla částečně vymazána. Problémy s: {$failedTypesStr}");
        }

        $this->redirect('/settings/system');
    }

    public function optimizeOpCache(): void
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!$this->session('user')) {
            $this->redirect('/login');
        }

        $success = optimize_opcache();

        if ($success) {
            $this->session('flash_message', 'OPcache byl úspěšně optimalizován.');
            $this->session('flash_type', 'success');
        } else {
            $this->session('flash_message', 'Chyba při optimalizaci OPcache.');
            $this->session('flash_type', 'error');
        }

        $this->redirect('/settings/system');
    }

    public function createBackup(): void
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!$this->session('user')) {
            $this->redirect('/login');
        }

        $success = create_backup();

        if ($success) {
            $this->session('flash_message', 'Záloha byla úspěšně vytvořena.');
            $this->session('flash_type', 'success');
        } else {
            $this->session('flash_message', 'Chyba při vytváření zálohy.');
            $this->session('flash_type', 'error');
        }

        $this->redirect('/settings/system');
    }

    public function systemLogs(): Response\ViewResponse
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!$this->session('user')) {
            $this->redirect('/login');
        }

        $logs = get_system_logs();

        return $this->view('settings.logs', [
            'logs' => $logs
        ]);
    }

    public function checkIntegrity(): void
    {
        // Kontrola oprávnění (zde by měla být kontrola role admin)
        if (!$this->session('user')) {
            $this->redirect('/login');
        }

        $integrityCheck = check_database_integrity();

        if ($integrityCheck['success']) {
            $this->session('flash_message', 'Kontrola integrity proběhla úspěšně. ' . $integrityCheck['message']);
            $this->session('flash_type', 'success');
        } else {
            $this->session('flash_message', 'Problémy s integritou databáze: ' . $integrityCheck['message']);
            $this->session('flash_type', 'error');
        }

        $this->redirect('/settings/system');
    }
}
