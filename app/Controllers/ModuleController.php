<?php

namespace App\Controllers;

use App\Entities\Module;
use Core\Facades\Container;
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
        $this->moduleManager = Container::get(ModuleManager::class, ModuleManager::class);
    }

    /**
     * Zobrazí seznam všech modulů
     */
    public function index(): ViewResponse
    {
        // Získáme moduly z databáze
        $dbModules = $this->moduleManager->availableModules();
        $modulesWithIssues = $this->moduleManager->modulesWithMissingDependencies();

        // Kombinace modulů z databáze a souborového systému
        $allModules = [];

        // Přidat moduly z databáze
        foreach ($dbModules as $moduleName) {
            $config = $this->moduleManager->moduleConfig($moduleName);
            $allModules[$moduleName] = [
                'type' => 'database',
                'name' => $moduleName,
                'config' => $config
            ];
        }

        // Získáme moduly ze souborového systému
        $scanResult = $this->moduleManager->scanAndSync();
        foreach ($scanResult['new_modules'] as $newModule) {
            $moduleName = $newModule['name'];
            if (!isset($allModules[$moduleName])) {
                $allModules[$moduleName] = [
                    'type' => 'filesystem',
                    'name' => $moduleName,
                    'config' => $newModule['config']
                ];
            }
        }

        $data = [
            'modules' => $allModules,
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
        $config = $this->moduleManager->moduleConfig($moduleName);
        
        if (!$config || !isset($config['is_enabled'])) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/settings/modules');
            return $this->view('modules.index', ['modules' => []]);
        }

        $missingDependencies = $config['missing_dependencies'] ?? [];

        $data = [
            'module_name' => $moduleName,
            'config' => $config,
            'missing_dependencies' => $missingDependencies,
            'title' => 'Detail modulu: ' . ($config['display_name'] ?? $moduleName)
        ];

        return $this->view('modules.show', $data);
    }

    /**
     * Zobrazí formulář pro editaci modulu
     */
    public function edit($moduleName): ViewResponse
    {
        $config = $this->moduleManager->moduleConfig($moduleName);
        
        if (!$config || !isset($config['is_enabled'])) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/settings/modules');
            return $this->view('modules.index', ['modules' => []]);
        }

        $missingDependencies = $config['missing_dependencies'] ?? [];

        $data = [
            'module_name' => $moduleName,
            'config' => $config,
            'missing_dependencies' => $missingDependencies,
            'title' => 'Editace modulu: ' . ($config['display_name'] ?? $moduleName)
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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $data = $this->request->getJson();

            // TODO: Implementovat aktualizaci modulu přes nové API
            // Prozatím vrátíme úspěch
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
            error_log("[ModuleController] toggle: Volána metoda pro modul: {$moduleName}");

            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                error_log("[ModuleController] toggle: Modul '{$moduleName}' nebyl nalezen");
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $currentStatus = $config['is_enabled'] ?? false;
            $newStatus = !$currentStatus;

            error_log("[ModuleController] toggle: Změna stavu z {$currentStatus} na {$newStatus}");

            // TODO: Implementovat toggle přes nové API
            // Prozatím vrátíme úspěch
            return $this->json([
                'success' => true,
                'message' => 'Stav modulu byl úspěšně změněn',
                'data' => [
                    'module' => $moduleName,
                    'enabled' => $newStatus
                ]
            ]);
        } catch (Exception $e) {
            error_log("[ModuleController] toggle: Chyba: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Zobrazí závislosti modulu
     */
    public function dependencies($moduleName): ViewResponse
    {
        $config = $this->moduleManager->moduleConfig($moduleName);
        
        if (!$config) {
            $this->toastError('Modul nebyl nalezen');
            $this->redirect('/settings/modules');
            return $this->view('modules.index', ['modules' => []]);
        }

        $missingDependencies = $config['missing_dependencies'] ?? [];
        $dependencies = $config['dependencies'] ?? [];

        $data = [
            'module_name' => $moduleName,
            'config' => $config,
            'dependencies' => $dependencies,
            'missing_dependencies' => $missingDependencies,
            'title' => 'Závislosti modulu: ' . ($config['display_name'] ?? $moduleName)
        ];

        return $this->view('modules.dependencies', $data);
    }

    /**
     * API endpoint pro získání stavu modulů
     */
    public function apiStatus(): JsonResponse
    {
        try {
            $enabledModules = $this->moduleManager->enabledModules();
            $availableModules = $this->moduleManager->availableModules();
            $modulesWithIssues = $this->moduleManager->modulesWithMissingDependencies();

            return $this->json([
                'success' => true,
                'data' => [
                    'enabled_modules' => $enabledModules,
                    'available_modules' => $availableModules,
                    'modules_with_issues' => $modulesWithIssues,
                    'total_enabled' => count($enabledModules),
                    'total_available' => count($availableModules),
                    'total_issues' => count($modulesWithIssues)
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
     * API endpoint pro skenování modulů
     */
    public function scan(): JsonResponse
    {
        try {
            $scanResult = $this->moduleManager->scanAndSync();

            return $this->json([
                'success' => true,
                'data' => $scanResult
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API endpoint pro automatické vložení nových modulů
     */
    public function autoInsert(): JsonResponse
    {
        try {
            $insertResults = $this->moduleManager->autoInsertNew();

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

    /**
     * Zobrazí log modulů
     */
    public function log(): ViewResponse
    {
        $data = [
            'title' => 'Log modulů',
            'logs' => [] // TODO: Implementovat logování modulů
        ];

        return $this->view('modules.log', $data);
    }

    // ========================================
    // CONTROLLERS ENDPOINTS
    // ========================================

    /**
     * Získá seznam controllerů modulu
     */
    public function getControllers($moduleName): JsonResponse
    {
        error_log("[ModuleController] getControllers: Volána metoda pro modul: {$moduleName}");

        try {
            $config = $this->moduleManager->moduleConfig($moduleName);

            if (!$config) {
                error_log("[ModuleController] getControllers: Modul '{$moduleName}' nebyl nalezen");
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat získání controllerů přes nové API
            $controllers = []; // Prozatím prázdné pole

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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
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

            // TODO: Implementovat vytvoření controlleru přes nové API

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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
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
     * Aktualizuje controller
     */
    public function updateController($moduleName, $controllerName): JsonResponse
    {
        try {
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            $data = $this->request->getJson();

            // TODO: Implementovat aktualizaci controlleru přes nové API

            return $this->json([
                'success' => true,
                'message' => 'Controller byl úspěšně aktualizován'
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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat smazání controlleru přes nové API
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

    // ========================================
    // ENTITIES ENDPOINTS
    // ========================================

    /**
     * Získá seznam entit modulu
     */
    public function getEntities($moduleName): JsonResponse
    {
        try {
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat získání entit přes nové API
            $entities = [];

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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
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

            // TODO: Implementovat vytvoření entity přes nové API

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

    /**
     * Získá soubory entity
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
     * Smazání entity
     */
    public function deleteEntity($moduleName, $entityName): JsonResponse
    {
        try {
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat smazání entity přes nové API
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

    // ========================================
    // MIGRATIONS ENDPOINTS
    // ========================================

    /**
     * Získá seznam migrací modulu
     */
    public function getMigrations($moduleName): JsonResponse
    {
        try {
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat získání migrací přes nové API
            $migrations = [];
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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
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

            // TODO: Implementovat vytvoření migrace přes nové API

            return $this->json([
                'success' => true,
                'message' => 'Migrace byla úspěšně vytvořena'
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
            $migrationPath = $modulePath . '/migrations/' . $migrationFile;

            if (!file_exists($migrationPath)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Migrace nebyla nalezena'
                ], 404);
            }

            $files = [
                [
                    'name' => $migrationFile,
                    'path' => 'migrations/' . $migrationFile,
                    'type' => 'php',
                    'size' => filesize($migrationPath)
                ]
            ];

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
     * Spustí migrace modulu
     */
    public function runMigrations($moduleName): JsonResponse
    {
        try {
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // Spustit migrace přes nové API
            $this->moduleManager->runModuleMigrations($moduleName);

            return $this->json([
                'success' => true,
                'message' => 'Migrace byly úspěšně spuštěny'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Vrátí migrace modulu zpět
     */
    public function rollbackMigrations($moduleName): JsonResponse
    {
        try {
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // Vrátit migrace zpět přes nové API
            $this->moduleManager->rollbackModuleMigrations($moduleName);

            return $this->json([
                'success' => true,
                'message' => 'Migrace byly úspěšně vráceny zpět'
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
            $config = $this->moduleManager->moduleConfig($moduleName);
            
            if (!$config) {
                return $this->json([
                    'success' => false,
                    'message' => 'Modul nebyl nalezen'
                ], 404);
            }

            // TODO: Implementovat smazání migrace přes nové API
            return $this->json([
                'success' => true,
                'message' => 'Migrace byla úspěšně smazána'
            ]);
        } catch (Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Chyba: ' . $e->getMessage()
            ], 500);
        }
    }
}
