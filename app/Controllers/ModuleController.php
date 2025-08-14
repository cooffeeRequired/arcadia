<?php

namespace App\Controllers;

use App\Entities\Module;
use Core\Modules\ModuleManager;
use Core\Render\BaseController;
use Core\Http\Response\JsonResponse;
use Core\Http\Response\ViewResponse;
use Doctrine\ORM\EntityManager;
use Exception;

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
    public function index(): ViewResponse
    {
        $dbModules = $this->moduleManager->getAllModules();
        $availableFiles = $this->moduleManager->getAvailableModuleFiles();
        $modulesWithIssues = $this->moduleManager->getModulesWithMissingDependencies();

        // Kombinace modulů z databáze a souborového systému
        $allModules = [];

        // Přidat moduly z databáze
        foreach ($dbModules as $module) {
            $allModules[$module->getName()] = [
                'type' => 'database',
                'module' => $module,
                'config' => $this->moduleManager->getModuleConfig($module->getName())
            ];
        }

        // Přidat moduly ze souborového systému, které nejsou v databázi
        foreach ($availableFiles as $moduleName) {
            if (!isset($allModules[$moduleName])) {
                $configFile = APP_ROOT . '/modules/' . $moduleName . '/config.php';
                if (file_exists($configFile)) {
                    $config = require $configFile;
                    $allModules[$moduleName] = [
                        'type' => 'filesystem',
                        'name' => $moduleName,
                        'config' => $config
                    ];
                }
            }
        }

        $data = [
            'modules' => $allModules,
            'available_files' => $availableFiles,
            'modules_with_issues' => $modulesWithIssues,
            'title' => 'Správa modulů'
        ];

        return $this->view('modules.index', $data);
    }

    /**
     * Zobrazí detail modulu
     */
    public function show(string $moduleName): ViewResponse
    {
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/modules');
            return $this->view('modules.index', ['modules' => []]);
        }

        $config = $this->moduleManager->getModuleConfig($moduleName);
        $missingDependencies = $this->moduleManager->checkDependencies($moduleName);

        $data = [
            'module' => $module,
            'config' => $config,
            'missing_dependencies' => $missingDependencies,
            'title' => 'Detail modulu: ' . $module->getDisplayName()
        ];

        return $this->view('modules.show', $data);
    }

    /**
     * Povolí modul
     */
    public function enable(string $moduleName): JsonResponse
    {
        try {
            if (!$this->moduleManager->moduleExists($moduleName)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Modul neexistuje v souborovém systému'
                ], 404);
            }

            // Zkontrolovat závislosti
            $missingDependencies = $this->moduleManager->checkDependencies($moduleName);
            if (!empty($missingDependencies)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Chybějící závislosti: ' . implode(', ', $missingDependencies)
                ], 400);
            }

            $success = $this->moduleManager->enableModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně povolen');
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Modul byl úspěšně povolen'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nepodařilo se povolit modul'
                ], 500);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zakáže modul
     */
    public function disable(string $moduleName): JsonResponse
    {
        try {
            $success = $this->moduleManager->disableModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně zakázán');
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Modul byl úspěšně zakázán'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nepodařilo se zakázat modul'
                ], 500);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Nainstaluje modul
     */
    public function install(string $moduleName): JsonResponse
    {
        try {
            if (!$this->moduleManager->moduleExists($moduleName)) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Modul neexistuje v souborovém systému'
                ], 404);
            }

            // Vytvořit modul v databázi, pokud neexistuje
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                // Načíst konfiguraci modulu
                $configFile = APP_CONFIGURATION['modules_dir'] . '/' . $moduleName . '/config.php';
                if (file_exists($configFile)) {
                    $config = require $configFile;
                    $module = $this->moduleManager->createModule(
                        $moduleName,
                        $config['display_name'] ?? $moduleName,
                        $config['version'] ?? '1.0.0',
                        $config['description'] ?? null,
                        $config['author'] ?? null
                    );
                } else {
                    $module = $this->moduleManager->createModule($moduleName, $moduleName, '1.0.0');
                }
            }

            $success = $this->moduleManager->installModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně nainstalován');
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Modul byl úspěšně nainstalován'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nepodařilo se nainstalovat modul'
                ], 500);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Odinstaluje modul
     */
    public function uninstall(string $moduleName): JsonResponse
    {
        try {
            $success = $this->moduleManager->uninstallModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně odinstalován');
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Modul byl úspěšně odinstalován'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nepodařilo se odinstalovat modul'
                ], 500);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aktualizuje nastavení modulu
     */
    public function updateSettings(string $moduleName): JsonResponse
    {
        try {
            $settings = $this->request->getJson();

            $success = $this->moduleManager->updateModuleSettings($moduleName, $settings);

            if ($success) {
                $this->toastSuccess('Nastavení modulu bylo aktualizováno');
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Nastavení modulu bylo aktualizováno'
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Nepodařilo se aktualizovat nastavení'
                ], 500);
            }
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí formulář pro nastavení modulu
     */
    public function settings(string $moduleName): ViewResponse
    {
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/modules');
            return $this->view('modules.index', ['modules' => []]);
        }

        $config = $this->moduleManager->getModuleConfig($moduleName);

        $data = [
            'module' => $module,
            'config' => $config,
            'title' => 'Nastavení modulu: ' . $module->getDisplayName()
        ];

        return $this->view('modules.settings', $data);
    }

    /**
     * Zobrazí seznam dostupných modulů k instalaci
     */
    public function available(): ViewResponse
    {
        $availableFiles = $this->moduleManager->getAvailableModuleFiles();
        $installedModules = $this->moduleManager->getAllModules();
        $installedNames = array_map(fn($module) => $module->getName(), $installedModules);

        $data = [
            'available_files' => $availableFiles,
            'installed_names' => $installedNames,
            'title' => 'Dostupné moduly'
        ];

        return $this->view('modules.available', $data);
    }

    /**
     * Zobrazí log modulů
     */
    public function log(): ViewResponse
    {
        // Zde by byl kód pro zobrazení logu modulů
        $data = [
            'title' => 'Log modulů'
        ];

        return $this->view('modules.log', $data);
    }

        /**
     * API endpoint pro získání stavu modulů
     */
    public function apiStatus(): JsonResponse
    {
        try {
            $dbModules = $this->moduleManager->getAllModules();
            $availableFiles = $this->moduleManager->getAvailableModuleFiles();
            $modulesWithIssues = $this->moduleManager->getModulesWithMissingDependencies();

            $enabledCount = 0;
            $disabledCount = 0;
            $modulesData = [];

            // Přidat moduly z databáze
            foreach ($dbModules as $module) {
                $isEnabled = $module->isEnabled();
                if ($isEnabled) {
                    $enabledCount++;
                } else {
                    $disabledCount++;
                }

                $modulesData[] = [
                    'name' => $module->getName(),
                    'display_name' => $module->getDisplayName(),
                    'enabled' => $isEnabled,
                    'installed' => $module->isInstalled(),
                    'version' => $module->getVersion(),
                    'has_issues' => isset($modulesWithIssues[$module->getName()]),
                    'type' => 'database'
                ];
            }

            // Přidat moduly ze souborového systému
            foreach ($availableFiles as $moduleName) {
                $existingModule = array_filter($modulesData, fn($m) => $m['name'] === $moduleName);
                if (empty($existingModule)) {
                    $configFile = APP_ROOT . '/modules/' . $moduleName . '/config.php';
                    if (file_exists($configFile)) {
                        $config = require $configFile;
                        $modulesData[] = [
                            'name' => $moduleName,
                            'display_name' => $config['display_name'] ?? ucfirst($moduleName),
                            'enabled' => false,
                            'installed' => false,
                            'version' => $config['version'] ?? '1.0.0',
                            'has_issues' => false,
                            'type' => 'filesystem'
                        ];
                        $disabledCount++;
                    }
                }
            }

            return new JsonResponse([
                'success' => true,
                'data' => [
                    'total' => count($modulesData),
                    'enabled' => $enabledCount,
                    'disabled' => $disabledCount,
                    'with_issues' => count($modulesWithIssues),
                    'modules' => $modulesData
                ]
            ]);
        } catch (Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }
}
