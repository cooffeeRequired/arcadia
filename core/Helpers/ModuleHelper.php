<?php

namespace Core\Helpers;

use Core\Modules\ModuleManager;

class ModuleHelper
{
    private static ?ModuleManager $moduleManager = null;

    /**
     * Získá instanci ModuleManager
     */
    private static function getModuleManager(): ModuleManager
    {
        if (self::$moduleManager === null) {
            self::$moduleManager = new ModuleManager();
        }
        return self::$moduleManager;
    }

    /**
     * Zkontroluje, zda je modul povolen
     */
    public static function isEnabled(string $module): bool
    {
        return self::getModuleManager()->isEnabled($module);
    }

    /**
     * Zkontroluje, zda je modul dostupný
     */
    public static function isAvailable(string $module): bool
    {
        return self::getModuleManager()->isAvailable($module);
    }

    /**
     * Zkontroluje, zda má uživatel oprávnění pro modul
     */
    public static function hasPermission(string $module, string $permission): bool
    {
        return self::getModuleManager()->hasPermission($module, $permission);
    }

    /**
     * Získá nastavení modulu
     */
    public static function getSetting(string $module, string $setting, mixed $default = null): mixed
    {
        return self::getModuleManager()->getSetting($module, $setting, $default);
    }

    /**
     * Získá všechny dostupné moduly
     */
    public static function getAvailableModules(): array
    {
        return self::getModuleManager()->getAvailableModules();
    }

    /**
     * Získá moduly s chybějícími závislostmi
     */
    public static function getModulesWithIssues(): array
    {
        return self::getModuleManager()->getModulesWithMissingDependencies();
    }

    /**
     * Získá název modulu pro zobrazení
     */
    public static function getModuleName(string $module): string
    {
        $names = [
            'projects' => 'Projekty',
            'reports' => 'Reporty',
            'email_workflow' => 'E-mail Workflow',
            'invoices' => 'Faktury',
            'customers' => 'Zákazníci',
            'contacts' => 'Kontakty',
            'deals' => 'Obchody',
            'activities' => 'Aktivity',
        ];

        return $names[$module] ?? ucfirst($module);
    }

    /**
     * Získá ikonu modulu
     */
    public static function getModuleIcon(string $module): string
    {
        $icons = [
            'projects' => 'folder',
            'reports' => 'bar-chart',
            'email_workflow' => 'mail',
            'invoices' => 'file-text',
            'customers' => 'users',
            'contacts' => 'user',
            'deals' => 'trending-up',
            'activities' => 'activity',
        ];

        return $icons[$module] ?? 'box';
    }

    /**
     * Získá barvu modulu
     */
    public static function getModuleColor(string $module): string
    {
        $colors = [
            'projects' => 'blue',
            'reports' => 'green',
            'email_workflow' => 'purple',
            'invoices' => 'orange',
            'customers' => 'indigo',
            'contacts' => 'pink',
            'deals' => 'teal',
            'activities' => 'yellow',
        ];

        return $colors[$module] ?? 'gray';
    }

    /**
     * Získá popis modulu
     */
    public static function getModuleDescription(string $module): string
    {
        $descriptions = [
            'projects' => 'Správa projektů s možností sledování času, událostí a souborů',
            'reports' => 'Generování reportů a analýz z dat systému',
            'email_workflow' => 'Automatizované e-mailové workflow a šablony',
            'invoices' => 'Správa faktur a platebních podmínek',
            'customers' => 'Správa zákazníků a jejich informací',
            'contacts' => 'Správa kontaktů a komunikace',
            'deals' => 'Sledování obchodních příležitostí a transakcí',
            'activities' => 'Sledování aktivit a úkolů',
        ];

        return $descriptions[$module] ?? 'Modul pro správu ' . $module;
    }

    /**
     * Získá stav modulu pro zobrazení
     */
    public static function getModuleStatus(string $module): array
    {
        $manager = self::getModuleManager();

        if (!$manager->isEnabled($module)) {
            return [
                'status' => 'disabled',
                'label' => 'Vypnutý',
                'color' => 'red',
                'icon' => 'x-circle'
            ];
        }

        if (!$manager->isAvailable($module)) {
            return [
                'status' => 'unavailable',
                'label' => 'Nedostupný',
                'color' => 'orange',
                'icon' => 'alert-triangle'
            ];
        }

        return [
            'status' => 'available',
            'label' => 'Dostupný',
            'color' => 'green',
            'icon' => 'check-circle'
        ];
    }

    /**
     * Získá závislosti modulu
     */
    public static function getModuleDependencies(string $module): array
    {
        $manager = self::getModuleManager();
        $config = $manager->getModuleConfig($module);

        return $config['dependencies'] ?? [];
    }

    /**
     * Zkontroluje, zda má modul chybějící závislosti
     */
    public static function hasMissingDependencies(string $module): bool
    {
        $manager = self::getModuleManager();
        $missing = $manager->checkDependencies($module);

        return !empty($missing);
    }

    /**
     * Získá chybějící závislosti modulu
     */
    public static function getMissingDependencies(string $module): array
    {
        $manager = self::getModuleManager();
        return $manager->checkDependencies($module);
    }

    /**
     * Získá moduly, které závisí na daném modulu
     */
    public static function getDependentModules(string $module): array
    {
        $manager = self::getModuleManager();
        $allConfig = $manager->getAllModulesConfig();
        $dependent = [];

        foreach ($allConfig as $modName => $config) {
            if (in_array($module, $config['dependencies'])) {
                $dependent[] = $modName;
            }
        }

        return $dependent;
    }

    /**
     * Získá statistiku modulů
     */
    public static function getModuleStats(): array
    {
        $manager = self::getModuleManager();
        $allModules = $manager->getAllModulesConfig();
        $availableModules = $manager->getAvailableModules();
        $modulesWithIssues = $manager->getModulesWithMissingDependencies();

        return [
            'total' => count($allModules),
            'available' => count($availableModules),
            'disabled' => count($allModules) - count($availableModules),
            'with_issues' => count($modulesWithIssues)
        ];
    }

    /**
     * Získá menu položky pro dostupné moduly
     */
    public static function getMenuItems(): array
    {
        $availableModules = self::getAvailableModules();
        $menuItems = [];

        foreach ($availableModules as $module) {
            $menuItems[] = [
                'name' => self::getModuleName($module),
                'icon' => self::getModuleIcon($module),
                'color' => self::getModuleColor($module),
                'url' => '/' . $module,
                'module' => $module
            ];
        }

        return $menuItems;
    }
}
