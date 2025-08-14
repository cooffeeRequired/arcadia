<?php

namespace Core\Middleware;

use Core\Http\Request;
use Core\Modules\ModuleManager;
use Exception;

class ModuleMiddleware
{
    private ModuleManager $moduleManager;

    public function __construct()
    {
        $this->moduleManager = new ModuleManager();
    }

    /**
     * Kontroluje, zda je modul dostupný
     */
    public function checkModule(string $moduleName): void
    {
        if (!$this->moduleManager->isAvailable($moduleName)) {
            throw new Exception("Modul '{$moduleName}' není dostupný nebo není povolen.");
        }
    }

    /**
     * Kontroluje, zda má uživatel oprávnění pro modul
     */
    public function checkPermission(string $moduleName, string $permission, ?string $userRole = null): void
    {
        if (!$this->moduleManager->hasPermission($moduleName, $permission, $userRole)) {
            throw new Exception("Nemáte oprávnění pro modul '{$moduleName}' a akci '{$permission}'.");
        }
    }

    /**
     * Middleware pro kontrolu modulu v route
     */
    public function handle(Request $request, string $moduleName): void
    {
        $this->checkModule($moduleName);
    }

    /**
     * Middleware pro kontrolu modulu a oprávnění
     */
    public function handleWithPermission(Request $request, string $moduleName, string $permission): void
    {
        $this->checkModule($moduleName);
        $this->checkPermission($moduleName, $permission);
    }

    /**
     * Získá seznam dostupných modulů
     */
    public function getAvailableModules(): array
    {
        return $this->moduleManager->getAvailableModules();
    }

    /**
     * Zkontroluje, zda je modul povolen
     */
    public function isModuleEnabled(string $moduleName): bool
    {
        return $this->moduleManager->isEnabled($moduleName);
    }

    /**
     * Zkontroluje, zda je modul nainstalován
     */
    public function isModuleInstalled(string $moduleName): bool
    {
        return $this->moduleManager->isInstalled($moduleName);
    }
}
