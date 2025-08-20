<?php

namespace App\Services;

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
     * Načte všechny moduly ze souborů a synchronizuje s databází - optimalizovaná verze
     */
    public function loadModulesFromFiles(): array
    {
        $modules = [];
        $moduleDirs = glob($this->modulesPath . '/*', GLOB_ONLYDIR);

        if (empty($moduleDirs)) {
            return $modules;
        }

        // Načti všechny existující moduly najednou místo jednoho po jednom
        $existingModules = $this->moduleRepository->findAll();
        $moduleMap = [];
        foreach ($existingModules as $module) {
            $moduleMap[$module->getName()] = $module;
        }

        foreach ($moduleDirs as $moduleDir) {
            $moduleName = basename($moduleDir);
            $configFile = $moduleDir . '/config.php';

            if (file_exists($configFile)) {
                $config = require $configFile;
                $modules[$moduleName] = $this->processModuleConfigOptimized($moduleName, $config, $moduleDir, $moduleMap);
            }
        }

        // Ulož všechny změny najednou
        $this->entityManager->flush();

        return $modules;
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
     * Zpracuje konfiguraci modulu a vytvoří/aktualizuje záznam v databázi - optimalizovaná verze
     */
    private function processModuleConfigOptimized(string $moduleName, array $config, string $moduleDir, array &$moduleMap): Module
    {
        // Použij mapu místo databázového dotazu
        $module = $moduleMap[$moduleName] ?? null;

        if (!$module) {
            // Vytvoř nový modul
            $module = new Module();
            $module->setName($moduleName);
            $moduleMap[$moduleName] = $module; // Přidej do mapy
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

        // Ulož modul (flush se provede později hromadně)
        $this->entityManager->persist($module);

        // Zpracuj controllery s optimalizací
        $this->processControllersOptimized($module, $config, $moduleDir);

        // Zpracuj entity s optimalizací
        $this->processEntitiesOptimized($module, $config, $moduleDir);

        // Zpracuj migrace s optimalizací
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
     * Zpracuje migrace modulu - optimalizováno pro batch loading
     */
    private function processMigrations(Module $module, array $config, string $moduleDir): void
    {
        $migrationsDir = $moduleDir . '/migrations';

        if (!is_dir($migrationsDir)) {
            return;
        }

        $migrationFiles = glob($migrationsDir . '/*.php');

        if (empty($migrationFiles)) {
            return;
        }

        // Načti všechny existující migrace pro tento modul najednou
        $repository = $this->entityManager->getRepository(ModuleMigration::class);
        $existingMigrations = $repository->findBy(['module' => $module]);

        // Vytvoř mapu existujících migrací pro rychlé vyhledávání
        $migrationMap = [];
        foreach ($existingMigrations as $migration) {
            $migrationMap[$migration->getName()] = $migration;
        }

        foreach ($migrationFiles as $migrationFile) {
            $migrationName = basename($migrationFile, '.php');
            $this->processMigrationOptimized($module, $migrationName, $migrationFile, $migrationMap);
        }
    }

    /**
     * Zpracuje jednotlivou migraci - stará metoda (ponechává se pro kompatibilitu)
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
     * Zpracuje jednotlivou migraci - optimalizovaná verze s předloadenou mapou
     * @throws NotSupported
     */
    private function processMigrationOptimized(Module $module, string $migrationName, string $filePath, array &$migrationMap): void
    {
        // Použij mapu místo databázového dotazu
        $migration = $migrationMap[$migrationName] ?? null;

        if (!$migration) {
            $migration = new ModuleMigration();
            $migration->setModule($module);
            $migration->setName($migrationName);
            $migrationMap[$migrationName] = $migration; // Přidej do mapy pro další použití
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
            error_log("Chyba při instalaci modulu {$moduleName}: " . $e->getMessage());
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
            error_log("Chyba při odinstalaci modulu {$moduleName}: " . $e->getMessage());
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
            error_log("Chyba při změně stavu modulu {$moduleName}: " . $e->getMessage());
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

    /**
     * Získá všechny moduly s jejich souvisejícími daty pomocí JOINů - super optimalizovaná verze
     */
    public function getAllModulesWithRelations(): array
    {
        $qb = $this->entityManager->createQueryBuilder();

        // Hlavní dotaz pro moduly s LEFT JOINy pro všechny související entity
        $qb->select('m', 'mc', 'me', 'mm')
            ->from(Module::class, 'm')
            ->leftJoin('m.controllers', 'mc')
            ->leftJoin('m.entities', 'me')
            ->leftJoin('m.migrations', 'mm')
            ->where('m.isEnabled = :enabled')
            ->setParameter('enabled', true);

        $modules = $qb->getQuery()->getResult();

        return $modules;
    }

    /**
     * Získá konkrétní modul s jeho souvisejícími daty pomocí JOINů
     */
    public function getModuleWithRelations(string $moduleName): ?Module
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('m', 'mc', 'me', 'mm')
            ->from(Module::class, 'm')
            ->leftJoin('m.controllers', 'mc')
            ->leftJoin('m.entities', 'me')
            ->leftJoin('m.migrations', 'mm')
            ->where('m.name = :name')
            ->setParameter('name', $moduleName);

        $result = $qb->getQuery()->getOneOrNullResult();

        return $result;
    }

    /**
     * Optimalizovaná verze zpracování controllerů
     */
    private function processControllersOptimized(Module $module, array $config, string $moduleDir): void
    {
        $controllersDir = $moduleDir . '/' . ($config['controllers'] ?? 'controllers');

        if (!is_dir($controllersDir)) {
            return;
        }

        $controllerFiles = glob($controllersDir . '/*.php');

        if (empty($controllerFiles)) {
            return;
        }

        // Načti všechny existující controllery pro tento modul najednou
        $repository = $this->entityManager->getRepository(ModuleController::class);
        $existingControllers = $repository->findBy(['module' => $module]);

        // Vytvoř mapu existujících controllerů
        $controllerMap = [];
        foreach ($existingControllers as $controller) {
            $controllerMap[$controller->getName()] = $controller;
        }

        foreach ($controllerFiles as $controllerFile) {
            $controllerName = basename($controllerFile, '.php');
            $this->processControllerOptimized($module, $controllerName, $controllerFile, $controllerMap);
        }
    }

    /**
     * Optimalizovaná verze zpracování controlleru
     */
    private function processControllerOptimized(Module $module, string $controllerName, string $filePath, array &$controllerMap): void
    {
        // Použij mapu místo databázového dotazu
        $controller = $controllerMap[$controllerName] ?? null;

        if (!$controller) {
            $controller = new ModuleController();
            $controller->setModule($module);
            $controller->setName($controllerName);
            $controllerMap[$controllerName] = $controller;
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
     * Optimalizovaná verze zpracování entit
     */
    private function processEntitiesOptimized(Module $module, array $config, string $moduleDir): void
    {
        $entitiesDir = $moduleDir . '/' . ($config['entities'] ?? 'entities');

        if (!is_dir($entitiesDir)) {
            return;
        }

        $entityFiles = glob($entitiesDir . '/*.php');

        if (empty($entityFiles)) {
            return;
        }

        // Načti všechny existující entity pro tento modul najednou
        $repository = $this->entityManager->getRepository(ModuleEntity::class);
        $existingEntities = $repository->findBy(['module' => $module]);

        // Vytvoř mapu existujících entit
        $entityMap = [];
        foreach ($existingEntities as $entity) {
            $entityMap[$entity->getName()] = $entity;
        }

        foreach ($entityFiles as $entityFile) {
            $entityName = basename($entityFile, '.php');
            $this->processEntityOptimized($module, $entityName, $entityFile, $entityMap);
        }
    }

    /**
     * Optimalizovaná verze zpracování entity
     */
    private function processEntityOptimized(Module $module, string $entityName, string $filePath, array &$entityMap): void
    {
        // Použij mapu místo databázového dotazu
        $entity = $entityMap[$entityName] ?? null;

        if (!$entity) {
            $entity = new ModuleEntity();
            $entity->setModule($module);
            $entity->setName($entityName);
            $entityMap[$entityName] = $entity;
        }

        $entity->setFilePath($filePath);
        $entity->setNamespace("Modules\\{$module->getName()}\\Entities");
        $entity->setExtends('Core\\Entity\\BaseEntity');

        // Načti informace o entitě
        $entityInfo = $this->extractEntityInfo($filePath);
        $entity->setTableName($entityInfo['table'] ?? strtolower($entityName));
        $entity->setProperties($entityInfo['properties'] ?? []);

        $this->entityManager->persist($entity);
    }
}
