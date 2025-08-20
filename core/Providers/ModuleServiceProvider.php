<?php

namespace Core\Providers;

use Core\Facades\Container;
use Core\Services\ModuleManager;

class ModuleServiceProvider
{
    /**
     * Registruje a načítá moduly při bootu aplikace
     */
    public static function boot(): void
    {
        try {
            // Vytvoření instance ModuleManager
            $moduleManager = new ModuleManager();

            // Registrace do containeru
            Container::set(ModuleManager::class, $moduleManager, ['app.module_manager']);

            // Načtení modulů ze souborů
            $modules = $moduleManager->loadModulesFromFiles();

            // Synchronizace s databází (pokud metoda existuje)
            if (method_exists($moduleManager, 'syncModulesWithDatabase')) {
                $moduleManager->syncModulesWithDatabase();
            }

            // Registrace modulů do containeru pro globální přístup
            Container::set('modules', $modules, ['app.modules']);

            // Načtení aktivních modulů
            $activeModules = $moduleManager->getActiveModules();
            Container::set('active_modules', $activeModules, ['app.active_modules']);

            // Logování informací o načtených modulech
            self::logModuleInfo($modules, $activeModules);

        } catch (\Exception $e) {
            // Logování chyby, ale neukončení aplikace
            error_log("Chyba při načítání modulů: " . $e->getMessage());
        }
    }

    /**
     * Loguje informace o načtených modulech
     */
    private static function logModuleInfo(array $allModules, array $activeModules): void
    {
        $totalModules = count($allModules);
        $activeCount = count($activeModules);

        $logMessage = sprintf(
            "Moduly načteny: %d celkem, %d aktivních",
            $totalModules,
            $activeCount
        );

        // Logování do souboru
        error_log($logMessage);

        // Pokud je development prostředí, vypíšeme i do konzole
        if (getenv('APP_ENV') === 'development') {
            echo "[ModuleServiceProvider] {$logMessage}\n";
        }
    }

    /**
     * Registruje routy pro moduly
     */
    public static function registerModuleRoutes(): void
    {
        try {
            $moduleManager = Container::get(ModuleManager::class);
            $activeModules = $moduleManager->getActiveModules();

            foreach ($activeModules as $module) {
                self::registerModuleRoute($module);
            }

        } catch (\Exception $e) {
            error_log("Chyba při registraci rout modulů: " . $e->getMessage());
        }
    }

    /**
     * Registruje routy pro konkrétní modul
     */
    private static function registerModuleRoute($module): void
    {
        $moduleName = $module->getName();
        $controllers = $module->getControllers();

        if (!$controllers) {
            return;
        }

        foreach ($controllers as $controller) {
            if (!$controller->isEnabled()) {
                continue;
            }

            // Zde by se registrovaly routy pro controller
            // Toto je placeholder pro budoucí implementaci
            $controllerName = $controller->getName();
            $namespace = $controller->getNamespace();

            // Logování pro debugging
            if (getenv('APP_ENV') === 'development') {
                echo "[ModuleServiceProvider] Registrován controller: {$controllerName} ({$namespace})\n";
            }
        }
    }

    /**
     * Načte překlady modulů
     */
    public static function loadModuleTranslations(): void
    {
        try {
            $moduleManager = Container::get(ModuleManager::class);
            $activeModules = $moduleManager->getActiveModules();

            foreach ($activeModules as $module) {
                self::loadModuleTranslation($module);
            }

        } catch (\Exception $e) {
            error_log("Chyba při načítání překladů modulů: " . $e->getMessage());
        }
    }

    /**
     * Načte překlady pro konkrétní modul
     */
    private static function loadModuleTranslation($module): void
    {
        $moduleName = $module->getName();
        $translationsPath = APP_ROOT . "/modules/{$moduleName}/translations";

        if (!is_dir($translationsPath)) {
            return;
        }

        // Načtení překladů pro různé jazyky
        $languages = ['cs', 'en']; // Podporované jazyky

        foreach ($languages as $lang) {
            $translationFile = "{$translationsPath}/{$lang}.php";

            if (file_exists($translationFile)) {
                $translations = require $translationFile;

                // Registrace překladů do TranslationHelper
                // Toto je placeholder pro budoucí implementaci
                if (getenv('APP_ENV') === 'development') {
                    echo "[ModuleServiceProvider] Načteny překlady pro modul {$moduleName} ({$lang})\n";
                }
            }
        }
    }

    /**
     * Spustí migrace pro nové moduly
     */
    public static function runModuleMigrations(): void
    {
        try {
            $moduleManager = Container::get(ModuleManager::class);
            $activeModules = $moduleManager->getActiveModules();

            foreach ($activeModules as $module) {
                self::runModuleMigration($module);
            }

        } catch (\Exception $e) {
            error_log("Chyba při spouštění migrací modulů: " . $e->getMessage());
        }
    }

    /**
     * Spustí migrace pro konkrétní modul
     */
    private static function runModuleMigration($module): void
    {
        $migrations = $module->getMigrations();

        if (!$migrations) {
            return;
        }

        foreach ($migrations as $migration) {
            if ($migration->getStatus() === 'pending') {
                try {
                    // Spuštění migrace
                    // Toto je placeholder pro budoucí implementaci
                    if (getenv('APP_ENV') === 'development') {
                        echo "[ModuleServiceProvider] Spuštěna migrace: {$migration->getName()} pro modul {$module->getName()}\n";
                    }
                } catch (\Exception $e) {
                    error_log("Chyba při spouštění migrace {$migration->getName()}: " . $e->getMessage());
                }
            }
        }
    }
}
