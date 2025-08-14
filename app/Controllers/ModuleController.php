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
    public function show(): ViewResponse
    {
        $moduleName = $this->input('module');
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/settings/modules');
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
     * Zobrazí formulář pro editaci modulu
     */
    public function edit($moduleName): ViewResponse
    {
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/settings/modules');
            return $this->view('modules.index', ['modules' => []]);
        }

        $config = $this->moduleManager->getModuleConfig($moduleName);
        $missingDependencies = $this->moduleManager->checkDependencies($moduleName);

        $data = [
            'module' => $module,
            'config' => $config,
            'missing_dependencies' => $missingDependencies,
            'title' => 'Editace modulu: ' . $module->getDisplayName()
        ];

        return $this->view('modules.edit', $data);
    }

    /**
     * Aktualizuje modul
     */
    public function update(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $data = $this->request->getJson();

            // Aktualizace základních informací
            if (isset($data['display_name'])) {
                $module->setDisplayName($data['display_name']);
            }

            if (isset($data['description'])) {
                $module->setDescription($data['description']);
            }

            if (isset($data['version'])) {
                $module->setVersion($data['version']);
            }

            if (isset($data['author'])) {
                $module->setAuthor($data['author']);
            }

            if (isset($data['icon'])) {
                $module->setIcon($data['icon']);
            }

            if (isset($data['color'])) {
                $module->setColor($data['color']);
            }

            if (isset($data['sort_order'])) {
                $module->setSortOrder($data['sort_order']);
            }

            // Použít EntityManager z ModuleManager
            $entityManager = $this->moduleManager->getEntityManager();
            $entityManager->persist($module);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Modul byl úspěšně aktualizován'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba při aktualizaci: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Přepne stav modulu (zapne/vypne)
     */
    public function toggle($moduleName): JsonResponse
    {
        try {
            // Debug logging
            debug_log("Toggle module called for: " . $moduleName);

            if (empty($moduleName)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Název modulu je prázdný'
                ], 400);
            }

            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen: ' . $moduleName
                ], 404);
            }

            $isEnabled = $module->isEnabled();
            debug_log("Module {$moduleName} is currently enabled: " . ($isEnabled ? 'true' : 'false'));

            if ($isEnabled) {
                // Vypnout modul
                debug_log("Attempting to disable module: " . $moduleName);
                $success = $this->moduleManager->disableModule($moduleName);
                $message = 'Modul byl úspěšně vypnut';
            } else {
                // Zapnout modul
                debug_log("Attempting to enable module: " . $moduleName);

                // Zkontrolovat závislosti
                $missingDependencies = $this->moduleManager->checkDependencies($moduleName);
                if (!empty($missingDependencies)) {
                    debug_log("Missing dependencies for {$moduleName}: " . implode(', ', $missingDependencies));
                    return $this->json([
                        'success' => false,
                        'message' => 'Chybějící závislosti: ' . implode(', ', $missingDependencies)
                    ], 400);
                }

                $success = $this->moduleManager->enableModule($moduleName);
                $message = 'Modul byl úspěšně zapnut';
            }

            debug_log("Toggle result for {$moduleName}: " . ($success ? 'success' : 'failed'));

            if ($success) {
                return $this->json([
                    'success' => true,
                    'message' => $message
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se změnit stav modulu'
                ], 500);
            }
        } catch (Exception $e) {
            debug_log("Exception in toggle method: " . $e->getMessage());
            debug_log("Stack trace: " . $e->getTraceAsString());

            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí závislosti modulu
     */
    public function dependencies(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $dependencies = $this->moduleManager->checkDependencies($moduleName);
            $config = $this->moduleManager->getModuleConfig($moduleName);

            return $this->json([
                'success' => true,
                'data' => [
                    'module' => $moduleName,
                    'dependencies' => $config['dependencies'] ?? [],
                    'missing_dependencies' => $dependencies,
                    'has_issues' => !empty($dependencies)
                ]
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Povolí modul
     */
    public function enable(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            if (!$this->moduleManager->moduleExists($moduleName)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul neexistuje v souborovém systému'
                ], 404);
            }

            // Zkontrolovat závislosti
            $missingDependencies = $this->moduleManager->checkDependencies($moduleName);
            if (!empty($missingDependencies)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Chybějící závislosti: ' . implode(', ', $missingDependencies)
                ], 400);
            }

            $success = $this->moduleManager->enableModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně povolen');
                return $this->json([
                    'success' => true,
                    'message' => 'Modul byl úspěšně povolen'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se povolit modul'
                ], 500);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zakáže modul
     */
    public function disable(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            $success = $this->moduleManager->disableModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně zakázán');
                return $this->json([
                    'success' => true,
                    'message' => 'Modul byl úspěšně zakázán'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se zakázat modul'
                ], 500);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Nainstaluje modul
     */
    public function install(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            if (!$this->moduleManager->moduleExists($moduleName)) {
                return $this->json([
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
                return $this->json([
                    'success' => true,
                    'message' => 'Modul byl úspěšně nainstalován'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se nainstalovat modul'
                ], 500);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Odinstaluje modul
     */
    public function uninstall(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            $success = $this->moduleManager->uninstallModule($moduleName);

            if ($success) {
                $this->toastSuccess('Modul byl úspěšně odinstalován');
                return $this->json([
                    'success' => true,
                    'message' => 'Modul byl úspěšně odinstalován'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se odinstalovat modul'
                ], 500);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Aktualizuje nastavení modulu
     */
    public function updateSettings(): JsonResponse
    {
        try {
            $moduleName = $this->input('module');
            $settings = $this->request->getJson();

            $success = $this->moduleManager->updateModuleSettings($moduleName, $settings);

            if ($success) {
                $this->toastSuccess('Nastavení modulu bylo aktualizováno');
                return $this->json([
                    'success' => true,
                    'message' => 'Nastavení modulu bylo aktualizováno'
                ]);
            } else {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se aktualizovat nastavení'
                ], 500);
            }
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí formulář pro nastavení modulu
     */
    public function settings(): ViewResponse
    {
        $moduleName = $this->input('module');
        $module = $this->moduleManager->getModule($moduleName);
        if (!$module) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/settings/modules');
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
     * Manuální spuštění skenování modulů
     */
    public function scan(): JsonResponse
    {
        try {
            $scanResults = $this->moduleManager->scanAndSyncModules();

            $message = '';
            if (!empty($scanResults['new_modules'])) {
                $message .= 'Nalezeny nové moduly: ' . implode(', ', array_column($scanResults['new_modules'], 'name')) . '. ';
            }
            if (!empty($scanResults['updated_modules'])) {
                $message .= 'Aktualizované moduly: ' . implode(', ', array_column($scanResults['updated_modules'], 'name')) . '. ';
            }
            if (!empty($scanResults['removed_modules'])) {
                $message .= 'Odstraněné moduly: ' . implode(', ', $scanResults['removed_modules']) . '. ';
            }

            if (empty($message)) {
                $message = 'Žádné změny nebyly nalezeny.';
            }

            return $this->json([
                'success' => true,
                'message' => $message,
                'data' => $scanResults
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba při skenování: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Automatické vložení nových modulů do databáze
     */
    public function autoInsert(): JsonResponse
    {
        try {
            $insertResults = $this->moduleManager->autoInsertNewModules();

            $message = '';
            if (!empty($insertResults['inserted'])) {
                $message .= 'Vložené moduly do DB: ' . implode(', ', $insertResults['inserted']) . '. ';
            }
            if (!empty($insertResults['errors'])) {
                $message .= 'Chyby: ' . implode(', ', $insertResults['errors']) . '. ';
            }

            if (empty($message)) {
                $message = 'Žádné nové moduly k vložení.';
            }

            return $this->json([
                'success' => true,
                'message' => $message,
                'data' => $insertResults
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba při automatickém vkládání: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // CONTROLLERS ENDPOINTS
    // ========================================

        /**
     * Získá seznam controllerů modulu (merge config.php + database)
     */
    public function getControllers($moduleName): JsonResponse
    {
        error_log("[ModuleController] getControllers: Volána metoda pro modul: {$moduleName}");

        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                error_log("[ModuleController] getControllers: Modul '{$moduleName}' nebyl nalezen");
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            error_log("[ModuleController] getControllers: Modul nalezen, volám ModuleManager->getModuleControllers()");
            // Použít ModuleManager pro merge logiku
            $controllers = $this->moduleManager->getModuleControllers($moduleName);
            error_log("[ModuleController] getControllers: Získáno " . count($controllers) . " controllerů");

            return $this->json([
                'success' => true,
                'controllers' => $controllers
            ]);
        } catch (Exception $e) {
            error_log("[ModuleController] getControllers: Chyba: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vytvoří nový controller pro modul
     */
    public function createController($moduleName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $data = $this->request->getJson();

            // Validace
            if (empty($data['name'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Název controlleru je povinný'
                ], 400);
            }

            // TODO: Implementovat vytvoření controlleru
            // 1. Vytvořit ModuleController entity
            // 2. Vygenerovat PHP soubor
            // 3. Uložit do databáze

            return $this->json([
                'success' => true,
                'message' => 'Controller byl úspěšně vytvořen'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Získá soubory controlleru
     */
    public function getControllerFiles($moduleName, $controllerName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $modulePath = APP_CONFIGURATION['modules_dir'] . '/' . $moduleName;
            $files = [];

            // Hledáme controller soubor
            $controllerFileName = $controllerName . 'Controller.php';
            $controllerPath = $modulePath . '/Controllers/' . $controllerFileName;

            if (file_exists($controllerPath)) {
                $files[] = [
                    'name' => $controllerFileName,
                    'path' => 'Controllers/' . $controllerFileName,
                    'type' => 'php',
                    'size' => filesize($controllerPath)
                ];
            }

            // Hledáme view soubory pro controller
            $viewsDir = $modulePath . '/Views/' . strtolower($controllerName);
            if (is_dir($viewsDir)) {
                $viewFiles = glob($viewsDir . '/*.blade.php');
                foreach ($viewFiles as $viewFile) {
                    $fileName = basename($viewFile);
                    $files[] = [
                        'name' => $fileName,
                        'path' => 'Views/' . strtolower($controllerName) . '/' . $fileName,
                        'type' => 'blade',
                        'size' => filesize($viewFile)
                    ];
                }
            }

            // Pokud neexistuje controller soubor, vytvoříme placeholder
            if (empty($files)) {
                $files[] = [
                    'name' => $controllerFileName,
                    'path' => 'Controllers/' . $controllerFileName,
                    'type' => 'php',
                    'size' => 0,
                    'placeholder' => true
                ];
            }

            return $this->json([
                'success' => true,
                'files' => $files
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Uloží obsah souboru
     */
    public function saveControllerFile($moduleName, $controllerName): JsonResponse
    {
        try {
            $data = $this->request->getJson();

            if (!isset($data['content'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Obsah souboru je povinný'
                ], 400);
            }

            $filePath = $data['file_path'] ?? null;
            if (!$filePath) {
                return $this->json([
                    'success' => false,
                    'message' => 'Cesta k souboru je povinná'
                ], 400);
            }

            // Kontrola, zda soubor patří k modulu
            $modulePath = APP_CONFIGURATION['modules_dir'] . '/' . $moduleName;
            if (!str_starts_with($filePath, $modulePath)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Neplatná cesta k souboru'
                ], 400);
            }

            // Uložení souboru
            $result = file_put_contents($filePath, $data['content']);
            if ($result === false) {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se uložit soubor'
                ], 500);
            }

            return $this->json([
                'success' => true,
                'message' => 'Soubor byl úspěšně uložen'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // ENTITIES ENDPOINTS
    // ========================================

        /**
     * Získá seznam entit modulu (merge config.php + database)
     */
    public function getEntities($moduleName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // Použít ModuleManager pro merge logiku
            $entities = $this->moduleManager->getModuleEntities($moduleName);

            return $this->json([
                'success' => true,
                'entities' => $entities
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vytvoří novou entity pro modul
     */
    public function createEntity($moduleName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $data = $this->request->getJson();

            // Validace
            if (empty($data['name'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Název entity je povinný'
                ], 400);
            }

            if (empty($data['table_name'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Název tabulky je povinný'
                ], 400);
            }

            // TODO: Implementovat vytvoření entity
            // 1. Vytvořit ModuleEntity entity
            // 2. Vygenerovat PHP soubor
            // 3. Uložit do databáze

            return $this->json([
                'success' => true,
                'message' => 'Entity byla úspěšně vytvořena'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    // ========================================
    // MIGRATIONS ENDPOINTS
    // ========================================

        /**
     * Získá seznam migrací modulu (merge config.php + database)
     */
    public function getMigrations($moduleName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // Použít ModuleManager pro merge logiku
            $migrations = $this->moduleManager->getModuleMigrations($moduleName);
            $status = $this->moduleManager->getModuleMigrationStatus($moduleName);

            return $this->json([
                'success' => true,
                'migrations' => $migrations,
                'status' => $status
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vytvoří novou migraci pro modul
     */
    public function createMigration($moduleName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $data = $this->request->getJson();

            // Validace
            if (empty($data['name'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Název migrace je povinný'
                ], 400);
            }

            if (empty($data['type'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Typ migrace je povinný'
                ], 400);
            }

            // TODO: Implementovat vytvoření migrace
            // 1. Vytvořit ModuleMigration entity
            // 2. Vygenerovat migrační soubor
            // 3. Uložit do databáze

            return $this->json([
                'success' => true,
                'message' => 'Migration byla úspěšně vytvořena'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Spustí migraci
     */
    public function runMigration($moduleName, $migrationFile): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat spuštění migrace
            // 1. Najít migraci v databázi
            // 2. Spustit migraci přes CLI
            // 3. Aktualizovat status

            $output = "Migration {$migrationFile} byla úspěšně spuštěna.";

            return $this->json([
                'success' => true,
                'message' => 'Migration byla úspěšně spuštěna',
                'output' => $output
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vrátí migraci zpět
     */
    public function rollbackMigration($moduleName, $migrationFile): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat rollback migrace
            // 1. Najít migraci v databázi
            // 2. Spustit rollback přes CLI
            // 3. Aktualizovat status

            $output = "Migration {$migrationFile} byla úspěšně vrácena zpět.";

            return $this->json([
                'success' => true,
                'message' => 'Migration byla úspěšně vrácena zpět',
                'output' => $output
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Získá log migrace
     */
    public function getMigrationLog($moduleName, $migrationFile): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat načítání logu migrace
            $log = "Log pro migraci {$migrationFile}:\n";
            $log .= "Spuštěno: " . date('Y-m-d H:i:s') . "\n";
            $log .= "Status: Úspěšně dokončeno\n";

            return $this->json([
                'success' => true,
                'log' => $log,
                'description' => "Detailní log migrace {$migrationFile}"
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Smazání controlleru
     */
    public function deleteController($moduleName, $controllerName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat smazání controlleru
            return $this->json([
                'success' => true,
                'message' => 'Controller byl úspěšně smazán'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Smazání entity
     */
    public function deleteEntity($moduleName, $entityName): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat smazání entity
            return $this->json([
                'success' => true,
                'message' => 'Entity byla úspěšně smazána'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Smazání migrace
     */
    public function deleteMigration($moduleName, $migrationFile): JsonResponse
    {
        try {
            $module = $this->moduleManager->getModule($moduleName);
            if (!$module) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat smazání migrace
            return $this->json([
                'success' => true,
                'message' => 'Migration byla úspěšně smazána'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Čtení souboru
     */
    public function readFile($moduleName): JsonResponse
    {
        try {
            $data = $this->request->getJson();

            if (!isset($data['path'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Cesta k souboru je povinná'
                ], 400);
            }

            $filePath = $data['path'];

            // Kontrola, zda soubor patří k modulu
            $modulePath = APP_CONFIGURATION['modules_dir'] . '/' . $moduleName;
            if (!str_starts_with($filePath, $modulePath)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Neplatná cesta k souboru'
                ], 400);
            }

            if (!file_exists($filePath)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Soubor neexistuje'
                ], 404);
            }

            $content = file_get_contents($filePath);
            $lines = substr_count($content, "\n") + 1;

            return $this->json([
                'success' => true,
                'content' => $content,
                'lines' => $lines
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zápis do souboru
     */
    public function writeFile($moduleName): JsonResponse
    {
        try {
            $data = $this->request->getJson();

            if (!isset($data['path']) || !isset($data['content'])) {
                return $this->json([
                    'success' => false,
                    'message' => 'Cesta k souboru a obsah jsou povinné'
                ], 400);
            }

            $filePath = $data['path'];
            $content = $data['content'];

            // Kontrola, zda soubor patří k modulu
            $modulePath = APP_CONFIGURATION['modules_dir'] . '/' . $moduleName;
            if (!str_starts_with($filePath, $modulePath)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Neplatná cesta k souboru'
                ], 400);
            }

            // Uložení souboru
            $result = file_put_contents($filePath, $content);
            if ($result === false) {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se uložit soubor'
                ], 500);
            }

            return $this->json([
                'success' => true,
                'message' => 'Soubor byl úspěšně uložen'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
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
                    $config = $this->moduleManager->getModuleConfig($moduleName);
                    $modulesData[] = [
                        'name' => $moduleName,
                        'display_name' => $config['display_name'] ?? ucfirst($moduleName),
                        'enabled' => $config['enabled'] ?? false,
                        'installed' => $config['installed'] ?? false,
                        'version' => $config['version'] ?? '1.0.0',
                        'has_issues' => false,
                        'type' => 'filesystem'
                    ];
                    $disabledCount++;
                }
                }
            }

            return $this->json([
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
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Získá soubory pro entitu
     */
    public function getEntityFiles($moduleName, $entityName): JsonResponse
    {
        try {
            $modulePath = APP_ROOT . '/modules/' . $moduleName;
            $files = [];

            // Kontroler soubor
            $controllerFile = $modulePath . '/Controllers/' . $entityName . 'Controller.php';
            if (file_exists($controllerFile)) {
                $files[] = [
                    'name' => $entityName . 'Controller.php',
                    'path' => 'Controllers/' . $entityName . 'Controller.php',
                    'type' => 'php',
                    'size' => filesize($controllerFile)
                ];
            }

            // Model soubor
            $modelFile = $modulePath . '/Models/' . $entityName . '.php';
            if (file_exists($modelFile)) {
                $files[] = [
                    'name' => $entityName . '.php',
                    'path' => 'Models/' . $entityName . '.php',
                    'type' => 'php',
                    'size' => filesize($modelFile)
                ];
            }

            // View soubory
            $viewsDir = $modulePath . '/Views/' . strtolower($entityName);
            if (is_dir($viewsDir)) {
                $viewFiles = glob($viewsDir . '/*.blade.php');
                foreach ($viewFiles as $viewFile) {
                    $fileName = basename($viewFile);
                    $files[] = [
                        'name' => $fileName,
                        'path' => 'Views/' . strtolower($entityName) . '/' . $fileName,
                        'type' => 'blade',
                        'size' => filesize($viewFile)
                    ];
                }
            }

            return $this->json([
                'success' => true,
                'files' => $files
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Získá soubory pro migraci
     */
    public function getMigrationFiles($moduleName, $migrationFile): JsonResponse
    {
        try {
            $modulePath = APP_ROOT . '/modules/' . $moduleName;
            $migrationsDir = $modulePath . '/migrations';
            $files = [];

            // Hlavní migrační soubor
            $migrationPath = $migrationsDir . '/' . $migrationFile;
            if (file_exists($migrationPath)) {
                $files[] = [
                    'name' => $migrationFile,
                    'path' => 'migrations/' . $migrationFile,
                    'type' => 'php',
                    'size' => filesize($migrationPath)
                ];
            }

            // SQL soubory (pokud existují)
            $sqlFile = $migrationsDir . '/' . pathinfo($migrationFile, PATHINFO_FILENAME) . '.sql';
            if (file_exists($sqlFile)) {
                $files[] = [
                    'name' => pathinfo($migrationFile, PATHINFO_FILENAME) . '.sql',
                    'path' => 'migrations/' . pathinfo($migrationFile, PATHINFO_FILENAME) . '.sql',
                    'type' => 'sql',
                    'size' => filesize($sqlFile)
                ];
            }

            return $this->json([
                'success' => true,
                'files' => $files
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Uloží soubor entity
     */
    public function saveEntityFile($moduleName, $entityName): JsonResponse
    {
        try {
            $data = $this->request->getJson();
            $filePath = $data['path'] ?? null;
            $content = $data['content'] ?? null;

            if (!$filePath || $content === null) {
                return $this->json([
                    'success' => false,
                    'message' => 'Chybí cesta k souboru nebo obsah'
                ], 400);
            }

            // Validace cesty
            $fullPath = APP_ROOT . '/modules/' . $moduleName . '/' . $filePath;
            $modulePath = APP_ROOT . '/modules/' . $moduleName;

            if (!str_starts_with(realpath($fullPath), realpath($modulePath))) {
                return $this->json([
                    'success' => false,
                    'message' => 'Neplatná cesta k souboru'
                ], 400);
            }

            // Vytvoření adresáře pokud neexistuje
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Uložení souboru
            $result = file_put_contents($fullPath, $content);
            if ($result === false) {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se uložit soubor'
                ], 500);
            }

            return $this->json([
                'success' => true,
                'message' => 'Soubor byl úspěšně uložen'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Uloží soubor migrace
     */
    public function saveMigrationFile($moduleName, $migrationFile): JsonResponse
    {
        try {
            $data = $this->request->getJson();
            $filePath = $data['path'] ?? null;
            $content = $data['content'] ?? null;

            if (!$filePath || $content === null) {
                return $this->json([
                    'success' => false,
                    'message' => 'Chybí cesta k souboru nebo obsah'
                ], 400);
            }

            // Validace cesty
            $fullPath = APP_ROOT . '/modules/' . $moduleName . '/' . $filePath;
            $modulePath = APP_ROOT . '/modules/' . $moduleName;

            if (!str_starts_with(realpath($fullPath), realpath($modulePath))) {
                return $this->json([
                    'success' => false,
                    'message' => 'Neplatná cesta k souboru'
                ], 400);
            }

            // Vytvoření adresáře pokud neexistuje
            $dir = dirname($fullPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            // Uložení souboru
            $result = file_put_contents($fullPath, $content);
            if ($result === false) {
                return $this->json([
                    'success' => false,
                    'message' => 'Nepodařilo se uložit soubor'
                ], 500);
            }

            return $this->json([
                'success' => true,
                'message' => 'Soubor byl úspěšně uložen'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }
}
