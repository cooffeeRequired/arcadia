<?php

namespace App\Controllers;

use Core\Http\Response;
use Core\Render\BaseController;
use Core\Services\ModuleManager;

class ModuleController extends BaseController
{
    private ModuleManager $moduleManager;

    public function __construct()
    {
        parent::__construct();
        $this->moduleManager = new ModuleManager();
    }

    /**
     * Zobrazí seznam všech modulů
     */
    public function index(): Response\ViewResponse
    {
        // Načti moduly ze souborů a synchronizuj s DB
        $this->moduleManager->loadModulesFromFiles();
        
        // Získej všechny moduly z databáze
        $modules = $this->moduleManager->getAllModules();
        
        return $this->view('settings.modules.index', [
            'modules' => $modules
        ]);
    }

    /**
     * Zobrazí detail modulu
     */
    public function show(string $moduleName): Response\ViewResponse
    {
        $module = $this->moduleManager->getModuleByName($moduleName);
        
        if (!$module) {
            $this->redirect('/settings/modules');
        }

        return $this->view('settings.modules.show', [
            'module' => $module
        ]);
    }

    /**
     * Nainstaluje modul
     */
    public function install(string $moduleName): void
    {
        if (!$this->hasPermission('admin')) {
            $this->redirect('/settings/modules');
        }

        $success = $this->moduleManager->installModule($moduleName);

        if ($success) {
            $this->toastSuccess("Modul {$moduleName} byl úspěšně nainstalován");
        } else {
            $this->toastError("Chyba při instalaci modulu {$moduleName}");
        }

        $this->redirect('/settings/modules');
    }

    /**
     * Odinstaluje modul
     */
    public function uninstall(string $moduleName): void
    {
        if (!$this->hasPermission('admin')) {
            $this->redirect('/settings/modules');
        }

        $success = $this->moduleManager->uninstallModule($moduleName);

        if ($success) {
            $this->toastSuccess("Modul {$moduleName} byl úspěšně odinstalován");
        } else {
            $this->toastError("Chyba při odinstalaci modulu {$moduleName}");
        }

        $this->redirect('/settings/modules');
    }

    /**
     * Zapne modul
     */
    public function enable(string $moduleName): void
    {
        if (!$this->hasPermission('admin')) {
            $this->redirect('/settings/modules');
        }

        $success = $this->moduleManager->toggleModule($moduleName, true);

        if ($success) {
            $this->toastSuccess("Modul {$moduleName} byl zapnut");
        } else {
            $this->toastError("Chyba při zapnutí modulu {$moduleName}");
        }

        $this->redirect('/settings/modules');
    }

    /**
     * Vypne modul
     */
    public function disable(string $moduleName): void
    {
        if (!$this->hasPermission('admin')) {
            $this->redirect('/settings/modules');
        }

        $success = $this->moduleManager->toggleModule($moduleName, false);

        if ($success) {
            $this->toastSuccess("Modul {$moduleName} byl vypnut");
        } else {
            $this->toastError("Chyba při vypnutí modulu {$moduleName}");
        }

        $this->redirect('/settings/modules');
    }

    /**
     * Synchronizuje moduly ze souborů
     */
    public function sync(): void
    {
        if (!$this->hasPermission('admin')) {
            $this->redirect('/settings/modules');
        }

        try {
            $this->moduleManager->loadModulesFromFiles();
            $this->toastSuccess("Moduly byly úspěšně synchronizovány");
        } catch (\Exception $e) {
            $this->toastError("Chyba při synchronizaci modulů: " . $e->getMessage());
        }

        $this->redirect('/settings/modules');
    }

    /**
     * Kontrola oprávnění
     */
    private function hasPermission(string $permission): bool
    {
        $user = $this->session('user');
        return $user && in_array($permission, $user['permissions'] ?? []);
    }
}
