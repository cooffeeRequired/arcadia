<?php

namespace Core\Services;

use App\Entities\Module;
use App\Entities\ModuleController;
use App\Entities\ModuleEntity;
use App\Entities\ModuleMigration;
use Core\Facades\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Exception;

class ModuleManager
{
    private EntityManager $entityManager;
    private EntityRepository $moduleRepository;
    private string $modulesPath;

    /**
     * @throws NotSupported
     */
    public function __construct()
    {
        $this->entityManager = Container::get(EntityManager::class);
        $this->moduleRepository = $this->entityManager->getRepository(Module::class);
        $this->modulesPath = dirname(__DIR__, 2) . '/modules';
    }

    /**
     * Načte všechny moduly ze souborů a synchronizuje s databází
     */
    public function loadModulesFromFiles(): array
    {
        $modules = [];
        $config = $this->getModuleConfig();

        // Použití konfigurace pro cestu k modulům
        $modulesPath = $config['modules_path'] ?? $this->modulesPath;
        $moduleDirs = glob($modulesPath . '/*', GLOB_ONLYDIR);

        foreach ($moduleDirs as $moduleDir) {
            $moduleName = basename($moduleDir);

            // Kontrola ignorovaných složek
            if (in_array($moduleName, $config['ignored_directories'] ?? [])) {
                continue;
            }

            $configFile = $moduleDir . '/config.php';

            if (file_exists($configFile)) {
                try {
                    $moduleConfig = require $configFile;
                    $modules[$moduleName] = $this->processModuleConfig($moduleName, $moduleConfig, $moduleDir);

                    // Logování pro debugging
                    if ($config['logging']['enabled'] && $config['logging']['level'] === 'debug') {
                        debug_log("Modul načten: {$moduleName}");
                    }
                } catch (\Exception $e) {
                    debug_log("Chyba při načítání modulu {$moduleName}: " . $e->getMessage());
                }
            }
        }

        return $modules;
    }

    /**
     * Získá konfiguraci modulů
     */
    private function getModuleConfig(): array
    {
        $configFile = dirname(__DIR__, 2) . '/config/modules.php';

        if (file_exists($configFile)) {
            return require $configFile;
        }

        // Výchozí konfigurace
        return [
            'modules_path' => $this->modulesPath,
            'ignored_directories' => ['.git', 'node_modules', 'vendor', 'cache', 'logs', 'temp', 'tests', 'docs'],
            'logging' => [
                'enabled' => true,
                'level' => 'info',
            ],
        ];
    }

    /**
     * Zpracuje konfiguraci modulu a vytvoří/aktualizuje záznam v databázi
     */
    private function processModuleConfig(string $moduleName, array $config, string $moduleDir): Module
    {
        // Najdi existující modul v databázi
        $module = $this->moduleRepository->findOneBy(['name' => $moduleName]);

        if (!$module) {
            // Vytvoř nový modul
            $module = new Module();
            $module->setName($moduleName);
        }

        // Aktualizuj základní informace
        $module->setDisplayName($config['display_name'] ?? $moduleName);
        $module->setDescription($config['description'] ?? null);
        $module->setVersion($config['version'] ?? '1.0.0');
        $module->setAuthor($config['author'] ?? null);
        $module->setDependencies($config['dependencies'] ?? null);
        $module->setPermissions($config['permissions'] ?? null);

        // Nastav menu informace
        if (isset($config['menu'])) {
            $module->setIcon($config['menu']['icon'] ?? null);
            $module->setSortOrder($config['menu']['order'] ?? null);
        }

        // Ulož modul
        $this->entityManager->persist($module);
        $this->entityManager->flush();

        // Zpracuj controllery
        $this->processControllers($module, $config, $moduleDir);

        // Zpracuj entity
        $this->processEntities($module, $config, $moduleDir);

        // Zpracuj migrace
        $this->processMigrations($module, $config, $moduleDir);

        // Zpracuj jazykové mutace
        $this->processTranslations($module, $moduleDir);

        return $module;
    }

    /**
     * Zpracuje controllery modulu
     */
    private function processControllers(Module $module, array $config, string $moduleDir): void
    {
        $controllersDir = $moduleDir . '/' . ($config['controllers'] ?? 'controllers');

        if (!is_dir($controllersDir)) {
            return;
        }

        $controllerFiles = glob($controllersDir . '/*.php');

        foreach ($controllerFiles as $controllerFile) {
            $controllerName = basename($controllerFile, '.php');
            $this->processController($module, $controllerName, $controllerFile);
        }
    }

    /**
     * Zpracuje jednotlivý controller
     * @throws NotSupported
     * @throws ORMException
     */
    private function processController(Module $module, string $controllerName, string $filePath): void
    {
        $repository = $this->entityManager->getRepository(ModuleController::class);
        $controller = $repository->findOneBy([
            'module' => $module,
            'name' => $controllerName
        ]);

        if (!$controller) {
            $controller = new ModuleController();
            $controller->setModule($module);
            $controller->setName($controllerName);
        }

        $controller->setFilePath($filePath);
        $controller->setNamespace("Modules\\{$module->getName()}\\Controllers");
        $controller->setExtends('Core\\Render\\BaseController');

        // Načti metody z controlleru
        $methods = $this->extractControllerMethods($filePath);
        $controller->setMethods($methods);

        $this->entityManager->persist($controller);
    }

    /**
     * Extrahuje metody z controller souboru
     */
    private function extractControllerMethods(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $methods = [];

        if (preg_match_all('/public\s+function\s+(\w+)\s*\(/', $content, $matches)) {
            $methods = $matches[1];
        }

        return $methods;
    }

    /**
     * Zpracuje entity modulu
     */
    private function processEntities(Module $module, array $config, string $moduleDir): void
    {
        $entitiesDir = $moduleDir . '/' . ($config['entities'] ?? 'entities');

        if (!is_dir($entitiesDir)) {
            return;
        }

        $entityFiles = glob($entitiesDir . '/*.php');

        foreach ($entityFiles as $entityFile) {
            $entityName = basename($entityFile, '.php');
            $this->processEntity($module, $entityName, $entityFile);
        }
    }

    /**
     * Zpracuje jednotlivou entitu
     * @throws NotSupported|ORMException
     */
    private function processEntity(Module $module, string $entityName, string $filePath): void
    {
        $repository = $this->entityManager->getRepository(ModuleEntity::class);
        $entity = $repository->findOneBy([
            'module' => $module,
            'name' => $entityName
        ]);

        if (!$entity) {
            $entity = new ModuleEntity();
            $entity->setModule($module);
            $entity->setName($entityName);
        }

        $entity->setFilePath($filePath);
        $entity->setNamespace("Modules\\{$module->getName()}\\Entities");
        $entity->setExtends('Core\\Entity\\BaseEntity');

        // Extrahuj informace o tabulce a vlastnostech
        $entityInfo = $this->extractEntityInfo($filePath);
        $entity->setTableName($entityInfo['table'] ?? strtolower($entityName));
        $entity->setProperties($entityInfo['properties'] ?? []);

        $this->entityManager->persist($entity);
    }

    /**
     * Extrahuje informace o entitě z souboru
     */
    private function extractEntityInfo(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $info = ['properties' => []];

        // Najdi název tabulky
        if (preg_match('/#\[ORM\\\Table\(name:\s*[\'"]([^\'"]+)[\'"]\)]/', $content, $matches)) {
            $info['table'] = $matches[1];
        }

        // Najdi vlastnosti
        if (preg_match_all('/#\[ORM\\\Column[^]]*]\s*protected\s+\$(\w+)/', $content, $matches)) {
            $info['properties'] = $matches[1];
        }

        return $info;
    }

    /**
     * Zpracuje migrace modulu
     */
    private function processMigrations(Module $module, array $config, string $moduleDir): void
    {
        $migrationsDir = $moduleDir . '/migrations';

        if (!is_dir($migrationsDir)) {
            return;
        }

        $migrationFiles = glob($migrationsDir . '/*.php');

        foreach ($migrationFiles as $migrationFile) {
            $migrationName = basename($migrationFile, '.php');
            $this->processMigration($module, $migrationName, $migrationFile);
        }
    }

    /**
     * Zpracuje jednotlivou migraci
     * @throws NotSupported
     */
    private function processMigration(Module $module, string $migrationName, string $filePath): void
    {
        $repository = $this->entityManager->getRepository(ModuleMigration::class);
        $migration = $repository->findOneBy([
            'module' => $module,
            'name' => $migrationName
        ]);

        if (!$migration) {
            $migration = new ModuleMigration();
            $migration->setModule($module);
            $migration->setName($migrationName);
        }

        $migration->setFile(basename($filePath));
        $migration->setType($this->determineMigrationType($migrationName));

        // Načti SQL obsah
        $sqlContent = $this->extractMigrationSql($filePath);
        $migration->setSqlContent($sqlContent);

        $this->entityManager->persist($migration);
    }

    /**
     * Určí typ migrace podle názvu
     */
    private function determineMigrationType(string $migrationName): string
    {
        if (str_contains($migrationName, 'install')) {
            return 'install';
        } elseif (str_contains($migrationName, 'uninstall')) {
            return 'uninstall';
        } else {
            return 'update';
        }
    }

    /**
     * Extrahuje SQL obsah z migrace
     */
    private function extractMigrationSql(string $filePath): ?string
    {
        $content = file_get_contents($filePath);

        // Hledej SQL v řetězcích
        if (preg_match('/return\s+[\'"]([^\'"]+)[\'"]\s*;/', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Zpracuje jazykové mutace modulu
     */
    private function processTranslations(Module $module, string $moduleDir): void
    {
        $translationFile = $moduleDir . '/translation.php';

        if (file_exists($translationFile)) {
            $translations = require $translationFile;
            // Zde by se uložily překlady do databáze nebo cache
        }
    }

    /**
     * Nainstaluje modul
     */
    public function installModule(string $moduleName): bool
    {
        try {
            $module = $this->moduleRepository->findOneBy(['name' => $moduleName]);

            if (!$module) {
                throw new Exception("Modul {$moduleName} nebyl nalezen");
            }

            // Spusť migrace
            $this->runModuleMigrations($module, 'install');

            // Označ jako nainstalovaný
            $module->setIsInstalled(true);
            $this->entityManager->flush();

            return true;
        } catch (Exception $e) {
            debug_log("Chyba při instalaci modulu {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Odinstaluje modul
     */
    public function uninstallModule(string $moduleName): bool
    {
        try {
            $module = $this->moduleRepository->findOneBy(['name' => $moduleName]);

            if (!$module) {
                throw new Exception("Modul {$moduleName} nebyl nalezen");
            }

            // Spusť uninstall migrace
            $this->runModuleMigrations($module, 'uninstall');

            // Označ jako neinstalovaný
            $module->setIsInstalled(false);
            $this->entityManager->flush();

            return true;
        } catch (Exception $e) {
            debug_log("Chyba při odinstalaci modulu {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Zapne/vypne modul
     */
    public function toggleModule(string $moduleName, bool $enabled): bool
    {
        try {
            $module = $this->moduleRepository->findOneBy(['name' => $moduleName]);

            if (!$module) {
                throw new Exception("Modul {$moduleName} nebyl nalezen");
            }

            $module->setIsEnabled($enabled);
            $this->entityManager->flush();

            return true;
        } catch (Exception $e) {
            debug_log("Chyba při změně stavu modulu {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Spustí migrace modulu
     */
    private function runModuleMigrations(Module $module, string $type): void
    {
        $repository = $this->entityManager->getRepository(ModuleMigration::class);
        $migrations = $repository->findBy([
            'module' => $module,
            'type' => $type,
            'status' => 'pending'
        ]);

        foreach ($migrations as $migration) {
            try {
                $this->executeMigration($migration);
                $migration->markAsRan();
            } catch (Exception $e) {
                $migration->markAsFailed($e->getMessage());
            }
        }

        $this->entityManager->flush();
    }

    /**
     * Spustí jednotlivou migraci
     */
    private function executeMigration(ModuleMigration $migration): void
    {
        $sql = $migration->getSqlContent();

        if ($sql) {
            $connection = $this->entityManager->getConnection();
            $connection->executeStatement($sql);
        }
    }

    /**
     * Získá všechny moduly
     */
    public function getAllModules(): array
    {
        return $this->moduleRepository->findAll();
    }

    /**
     * Získá aktivní moduly
     */
    public function getActiveModules(): array
    {
        return $this->moduleRepository->findBy(['isEnabled' => true]);
    }

    /**
     * Získá nainstalované moduly
     */
    public function getInstalledModules(): array
    {
        return $this->moduleRepository->findBy(['isInstalled' => true]);
    }
}
