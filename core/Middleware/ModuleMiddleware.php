<?php

namespace Core\Middleware;

use Core\Facades\Container;
use Core\Services\ModuleManager;

class ModuleMiddleware
{
    /**
     * Spustí middleware pro načítání modulů
     */
    public static function handle(): void
    {
        try {
            // Kontrola, zda jsou moduly již načteny
            if (!Container::get('modules')) {
                // Pokud nejsou načteny, načteme je
                $moduleManager = new ModuleManager();
                $modules = $moduleManager->loadModulesFromFiles();

                Container::set('modules', $modules, ['app.modules']);

                // Načtení aktivních modulů
                $activeModules = $moduleManager->getActiveModules();
                Container::set('active_modules', $activeModules, ['app.active_modules']);

                // Logování pro debugging
                if (getenv('APP_ENV') === 'development') {
                    error_log("Moduly načteny v middleware: " . count($modules) . " celkem, " . count($activeModules) . " aktivních");
                }
            }

        } catch (\Exception $e) {
            // Logování chyby, ale neukončení requestu
            error_log("Chyba v ModuleMiddleware: " . $e->getMessage());
        }
    }

    /**
     * Zkontroluje, zda je modul aktivní pro aktuální request
     */
    public static function checkModuleAccess(string $moduleName): bool
    {
        try {
            $activeModules = Container::get('active_modules') ?? [];

            foreach ($activeModules as $module) {
                if ($module->getName() === $moduleName) {
                    return $module->isInstalled() && $module->isEnabled();
                }
            }

            return false;

        } catch (\Exception $e) {
            error_log("Chyba při kontrole přístupu k modulu {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Získá seznam aktivních modulů pro aktuální request
     */
    public static function getActiveModules(): array
    {
        try {
            return Container::get('active_modules') ?? [];
        } catch (\Exception $e) {
            error_log("Chyba při získávání aktivních modulů: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Zkontroluje, zda má uživatel oprávnění pro modul
     */
    public static function checkModulePermission(string $moduleName, string $action): bool
    {
        try {
            $modules = Container::get('modules') ?? [];

            foreach ($modules as $module) {
                if ($module->getName() === $moduleName) {
                    $permissions = $module->getPermissions();

                    if (!$permissions) {
                        return true; // Pokud nejsou definována oprávnění, povolíme přístup
                    }

                    if (isset($permissions[$action])) {
                        $requiredRoles = $permissions[$action];

                        // Zde by byla kontrola role uživatele
                        // Prozatím vrátíme true
                        return true;
                    }

                    return false;
                }
            }

            return false;

        } catch (\Exception $e) {
            error_log("Chyba při kontrole oprávnění pro modul {$moduleName}: " . $e->getMessage());
            return false;
        }
    }
}
