<?php

namespace Core\Modules;

use App\Entities\Module;
use Core\Facades\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Exception;

class ModuleManager
{
    private EntityManager $entityManager;
    private array $config;

    public function __construct()
    {
        $this->entityManager = Container::get('doctrine.em');
        $this->config = require APP_ROOT . '/config/config.php';
    }

    /**
     * Získá EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * Zkontroluje, zda je modul povolen
     */
    public function isEnabled(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);
        return $module ? $module->isEnabled() : false;
    }

    /**
     * Zkontroluje, zda je modul nainstalován
     */
    public function isInstalled(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);
        return $module ? $module->isInstalled() : false;
    }

    /**
     * Zkontroluje, zda jsou splněny závislosti modulu
     */
    public function checkDependencies(string $moduleName): array
    {
        $module = $this->getModule($moduleName);
        if (!$module || !$module->getDependencies()) {
            return [];
        }

        $missing = [];
        foreach ($module->getDependencies() as $dependency) {
            if (!$this->isEnabled($dependency)) {
                $missing[] = $dependency;
            }
        }

        return $missing;
    }

    /**
     * Zkontroluje, zda je modul dostupný (povolen, nainstalován a závislosti splněny)
     */
    public function isAvailable(string $moduleName): bool
    {
        if (!$this->isEnabled($moduleName)) {
            return false;
        }

        if (!$this->isInstalled($moduleName)) {
            return false;
        }

        $missingDependencies = $this->checkDependencies($moduleName);
        return empty($missingDependencies);
    }

    /**
     * Získá nastavení modulu
     */
    public function getSetting(string $moduleName, string $setting, mixed $default = null): mixed
    {
        $module = $this->getModule($moduleName);
        if (!$module || !$module->getSettings()) {
            return $default;
        }

        return $module->getSettings()[$setting] ?? $default;
    }

    /**
     * Zkontroluje, zda má uživatel oprávnění pro modul
     */
    public function hasPermission(string $moduleName, string $permission, ?string $userRole = null): bool
    {
        if (!$this->isAvailable($moduleName)) {
            return false;
        }

        $module = $this->getModule($moduleName);
        if (!$module || !$module->getPermissions()) {
            return true; // Pokud nejsou definována oprávnění, povolíme přístup
        }

        $modulePermissions = $module->getPermissions();
        $permissionRoles = $modulePermissions[$permission] ?? [];

        if (empty($permissionRoles)) {
            return true; // Pokud nejsou definována oprávnění, povolíme přístup
        }

        if ($userRole === null) {
            // Zkusíme získat roli z session
            $session = Container::get('session');
            $userRole = $session->get('user_role', 'user');
        }

        return in_array($userRole, $permissionRoles);
    }

    /**
     * Získá všechny povolené moduly
     */
    public function getEnabledModules(): array
    {
        $modules = $this->entityManager->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->where('m.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();

        return array_map(fn($module) => $module->getName(), $modules);
    }

    /**
     * Získá všechny dostupné moduly (povolené, nainstalované se splněnými závislostmi)
     */
    public function getAvailableModules(): array
    {
        $modules = $this->entityManager->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->where('m.isEnabled = :enabled')
            ->andWhere('m.isInstalled = :installed')
            ->setParameter('enabled', true)
            ->setParameter('installed', true)
            ->getQuery()
            ->getResult();

        $available = [];
        foreach ($modules as $module) {
            if ($this->isAvailable($module->getName())) {
                $available[] = $module->getName();
            }
        }

        return $available;
    }

    /**
     * Získá moduly s chybějícími závislostmi
     * @throws NotSupported
     */
    public function getModulesWithMissingDependencies(): array
    {
        $modules = $this->entityManager->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->where('m.isEnabled = :enabled')
            ->setParameter('enabled', true)
            ->getQuery()
            ->getResult();

        $modulesWithIssues = [];
        foreach ($modules as $module) {
            $missingDependencies = $this->checkDependencies($module->getName());
            if (!empty($missingDependencies)) {
                $modulesWithIssues[$module->getName()] = $missingDependencies;
            }
        }

        return $modulesWithIssues;
    }

    /**
     * Získá konfiguraci modulu ze souboru config.php a přidá stav z databáze
     */
    public function getModuleConfig(string $moduleName): array
    {
        $configFile = $this->config['modules_dir'] . '/' . $moduleName . '/config.php';

        if (!file_exists($configFile)) {
            return [];
        }

        try {
            $config = require $configFile;
            if (!is_array($config)) {
                return [];
            }

            // Získat stav modulu z databáze
            $module = $this->getModule($moduleName);
            if ($module) {
                // Přidat stav z databáze do konfigurace
                $config['is_enabled'] = $module->isEnabled();
                $config['is_installed'] = $module->isInstalled();
                $config['enabled'] = $module->isEnabled(); // Pro kompatibilitu
                $config['installed'] = $module->isInstalled(); // Pro kompatibilitu
                $config['is_available'] = $this->isAvailable($moduleName);
                $config['available'] = $this->isAvailable($moduleName); // Pro kompatibilitu
            } else {
                // Modul není v databázi - nastavit jako vypnutý a nenainstalovaný
                $config['is_enabled'] = false;
                $config['is_installed'] = false;
                $config['enabled'] = false; // Pro kompatibilitu
                $config['installed'] = false; // Pro kompatibilitu
                $config['is_available'] = false;
                $config['available'] = false; // Pro kompatibilitu
            }

            return $config;
        } catch (Exception $e) {
            return [];
        }
    }

        /**
     * Získá controllery modulu (merge config.php + database)
     */
    public function getModuleControllers(string $moduleName): array
    {
        debug_log("[ModuleManager] getModuleControllers: Začínám načítat controllery pro modul: {$moduleName}");

        // Načíst controllery z config.php
        $configControllers = $this->getControllersFromConfig($moduleName);
        debug_log("[ModuleManager] getModuleControllers: Načteno " . count($configControllers) . " controllerů z config.php");

        // Načíst controllery z databáze
        $dbControllers = $this->getControllersFromDatabase($moduleName);
        debug_log("[ModuleManager] getModuleControllers: Načteno " . count($dbControllers) . " controllerů z databáze");

        // Merge controllery
        $mergedControllers = $this->mergeControllers($configControllers, $dbControllers);
        debug_log("[ModuleManager] getModuleControllers: Sloučeno celkem " . count($mergedControllers) . " controllerů");

        return $mergedControllers;
    }

        /**
     * Získá entity modulu (merge config.php + database)
     */
    public function getModuleEntities(string $moduleName): array
    {
        debug_log("[ModuleManager] getModuleEntities: Začínám načítat entity pro modul: {$moduleName}");

        // Načíst entity z config.php
        $configEntities = $this->getEntitiesFromConfig($moduleName);
        debug_log("[ModuleManager] getModuleEntities: Načteno " . count($configEntities) . " entit z config.php");

        // Načíst entity z databáze
        $dbEntities = $this->getEntitiesFromDatabase($moduleName);
        debug_log("[ModuleManager] getModuleEntities: Načteno " . count($dbEntities) . " entit z databáze");

        // Merge entity
        $mergedEntities = $this->mergeEntities($configEntities, $dbEntities);
        debug_log("[ModuleManager] getModuleEntities: Sloučeno celkem " . count($mergedEntities) . " entit");

        return $mergedEntities;
    }

        /**
     * Získá migrace modulu (merge config.php + database)
     */
    public function getModuleMigrations(string $moduleName): array
    {
        debug_log("[ModuleManager] getModuleMigrations: Začínám načítat migrace pro modul: {$moduleName}");

        // Načíst migrace z config.php
        $configMigrations = $this->getMigrationsFromConfig($moduleName);
        debug_log("[ModuleManager] getModuleMigrations: Načteno " . count($configMigrations) . " migrací z config.php");

        // Načíst migrace z databáze
        $dbMigrations = $this->getMigrationsFromDatabase($moduleName);
        debug_log("[ModuleManager] getModuleMigrations: Načteno " . count($dbMigrations) . " migrací z databáze");

        // Merge migrace
        $mergedMigrations = $this->mergeMigrations($configMigrations, $dbMigrations);
        debug_log("[ModuleManager] getModuleMigrations: Sloučeno celkem " . count($mergedMigrations) . " migrací");

        return $mergedMigrations;
    }

    /**
     * Získá statistiky migrací modulu
     */
    public function getModuleMigrationStatus(string $moduleName): array
    {
        $migrations = $this->getModuleMigrations($moduleName);
        return $this->calculateMigrationStatus($migrations);
    }

        /**
     * Načte controllery z config.php souboru modulu
     */
    private function getControllersFromConfig(string $moduleName): array
    {
        $configFile = $this->config['modules_dir'] . '/' . $moduleName . '/config.php';
        debug_log("[ModuleManager] getControllersFromConfig: Kontroluji config soubor: {$configFile}");

        if (!file_exists($configFile)) {
            debug_log("[ModuleManager] getControllersFromConfig: Config soubor neexistuje: {$configFile}");
            return [];
        }

        $config = require $configFile;
        debug_log("[ModuleManager] getControllersFromConfig: Config soubor načten, kontroluji sekci 'controllers'");

        if (!isset($config['controllers']) || !is_array($config['controllers'])) {
            debug_log("[ModuleManager] getControllersFromConfig: Sekce 'controllers' neexistuje nebo není pole");
            return [];
        }

        $controllers = [];
        foreach ($config['controllers'] as $controllerName => $controllerConfig) {
            debug_log("[ModuleManager] getControllersFromConfig: Načítám controller: {$controllerName}");
            $controllers[$controllerName] = [
                'name' => $controllerName,
                'namespace' => $controllerConfig['namespace'] ?? "Modules\\{$moduleName}\\Controllers",
                'extends' => $controllerConfig['extends'] ?? 'Core\Controllers\BaseController',
                'methods' => $controllerConfig['methods'] ?? [],
                'file_path' => $this->config['modules_dir'] . '/' . $moduleName . '/Controllers/' . $controllerName . 'Controller.php',
                'enabled' => $controllerConfig['enabled'] ?? true,
                'source' => 'config',
                'created_at' => date('Y-m-d H:i:s'),
                'methods_count' => count($controllerConfig['methods'] ?? []),
                'lines_count' => 0
            ];
        }

        debug_log("[ModuleManager] getControllersFromConfig: Načteno " . count($controllers) . " controllerů z config.php");
        return $controllers;
    }

        /**
     * Načte controllery z databáze
     */
    private function getControllersFromDatabase(string $moduleName): array
    {
        debug_log("[ModuleManager] getControllersFromDatabase: Začínám načítat controllery z databáze pro modul: {$moduleName}");

        try {
            $repository = $this->entityManager->getRepository(\App\Entities\ModuleController::class);
            debug_log("[ModuleManager] getControllersFromDatabase: Repository získán");

            // Najít modul podle názvu
            $module = $this->getModule($moduleName);
            if (!$module) {
                debug_log("[ModuleManager] getControllersFromDatabase: Modul '{$moduleName}' nebyl nalezen v databázi");
                return [];
            }

            debug_log("[ModuleManager] getControllersFromDatabase: Modul nalezen, ID: " . $module->getId());
            $dbControllers = $repository->findBy(['module' => $module]);
            debug_log("[ModuleManager] getControllersFromDatabase: Nalezeno " . count($dbControllers) . " controllerů v databázi");

            $controllers = [];
            foreach ($dbControllers as $controller) {
                debug_log("[ModuleManager] getControllersFromDatabase: Načítám controller z DB: " . $controller->getName());
                $controllers[$controller->getName()] = [
                    'id' => $controller->getId(),
                    'name' => $controller->getName(),
                    'namespace' => $controller->getNamespace(),
                    'extends' => $controller->getExtends(),
                    'methods' => $controller->getMethods(),
                    'file_path' => $controller->getFilePath(),
                    'enabled' => $controller->isEnabled(),
                    'source' => 'database',
                    'created_at' => $controller->getCreatedAt()->format('Y-m-d H:i:s'),
                    'methods_count' => $controller->getMethodsCount(),
                    'lines_count' => $controller->jsonSerialize()['lines_count']
                ];
            }

            debug_log("[ModuleManager] getControllersFromDatabase: Úspěšně načteno " . count($controllers) . " controllerů z databáze");
            return $controllers;
        } catch (Exception $e) {
            debug_log("[ModuleManager] getControllersFromDatabase: Chyba při načítání z databáze: " . $e->getMessage());
            // Pokud databáze není dostupná, vrátit prázdné pole
            return [];
        }
    }

    /**
     * Načte entity z config.php souboru modulu
     */
    private function getEntitiesFromConfig(string $moduleName): array
    {
        $configFile = $this->config['modules_dir'] . '/' . $moduleName . '/config.php';

        if (!file_exists($configFile)) {
            return [];
        }

        $config = require $configFile;

        if (!isset($config['entities']) || !is_array($config['entities'])) {
            return [];
        }

        $entities = [];
        foreach ($config['entities'] as $entityName => $entityConfig) {
            $entities[$entityName] = [
                'name' => $entityName,
                'table' => $entityConfig['table'] ?? strtolower($entityName) . 's',
                'table_name' => $entityConfig['table'] ?? strtolower($entityName) . 's',
                'namespace' => $entityConfig['namespace'] ?? "Modules\\{$moduleName}\\Models",
                'extends' => $entityConfig['extends'] ?? 'Core\Models\BaseModel',
                'properties' => $entityConfig['properties'] ?? [],
                'file_path' => $this->config['modules_dir'] . '/' . $moduleName . '/Models/' . $entityName . '.php',
                'table_exists' => false, // TODO: Kontrola existence tabulky
                'source' => 'config',
                'created_at' => date('Y-m-d H:i:s'),
                'columns_count' => count($entityConfig['properties'] ?? []),
                'lines_count' => 0
            ];
        }

        return $entities;
    }

    /**
     * Načte entity z databáze
     */
    private function getEntitiesFromDatabase(string $moduleName): array
    {
        try {
            $repository = $this->entityManager->getRepository(\App\Entities\ModuleEntity::class);

            // Najít modul podle názvu
            $module = $this->getModule($moduleName);
            if (!$module) {
                return [];
            }

            $dbEntities = $repository->findBy(['module' => $module]);

            $entities = [];
            foreach ($dbEntities as $entity) {
                $entities[$entity->getName()] = [
                    'id' => $entity->getId(),
                    'name' => $entity->getName(),
                    'table' => $entity->getTableName(),
                    'table_name' => $entity->getTableName(),
                    'namespace' => $entity->getNamespace(),
                    'extends' => $entity->getExtends(),
                    'properties' => $entity->getProperties(),
                    'file_path' => $entity->getFilePath(),
                    'table_exists' => $entity->isTableExists(),
                    'source' => 'database',
                    'created_at' => $entity->getCreatedAt()->format('Y-m-d H:i:s'),
                    'columns_count' => $entity->getColumnsCount(),
                    'lines_count' => $entity->jsonSerialize()['lines_count']
                ];
            }

            return $entities;
        } catch (Exception $e) {
            // Pokud databáze není dostupná, vrátit prázdné pole
            return [];
        }
    }

    /**
     * Načte migrace z config.php souboru modulu
     */
    private function getMigrationsFromConfig(string $moduleName): array
    {
        $migrationsDir = $this->config['modules_dir'] . '/' . $moduleName . '/migrations';
        debug_log("[ModuleManager] getMigrationsFromConfig: Kontroluji složku migrací: {$migrationsDir}");

        if (!is_dir($migrationsDir)) {
            debug_log("[ModuleManager] getMigrationsFromConfig: Složka migrací neexistuje: {$migrationsDir}");
            return [];
        }

        // Načíst definice migrací z config.php
        $configFile = $this->config['modules_dir'] . '/' . $moduleName . '/config.php';
        $configMigrations = [];

        if (file_exists($configFile)) {
            $config = require $configFile;
            if (isset($config['migrations']) && is_array($config['migrations'])) {
                $configMigrations = $config['migrations'];
            }
        }

        $migrations = [];
        $migrationFiles = glob($migrationsDir . '/*.php');

        // Filtrovat soubory install.php a uninstall.php
        $migrationFiles = array_filter($migrationFiles, function($file) {
            $fileName = basename($file);
            return !in_array($fileName, ['install.php', 'uninstall.php']);
        });

        debug_log("[ModuleManager] getMigrationsFromConfig: Nalezeno " . count($migrationFiles) . " migračních souborů (po filtrování)");

        foreach ($migrationFiles as $migrationFile) {
            $fileName = basename($migrationFile);
            $migrationName = pathinfo($fileName, PATHINFO_FILENAME);

            // Načíst obsah souboru
            $phpContent = file_get_contents($migrationFile);
            $linesCount = substr_count($phpContent, "\n") + 1;

            // Zjistit typ migrace a tabulku z názvu souboru nebo obsahu
            $type = 'create_table';
            $tableName = null;

            // Parsovat název souboru pro získání informací
            if (preg_match('/create_(\w+)_table/', $fileName, $matches)) {
                $tableName = $matches[1];
            }

            // Zkusit získat informace z obsahu souboru
            if (preg_match('/createTable\([\'"]([^\'"]+)[\'"]/', $phpContent, $matches)) {
                $tableName = $matches[1];
            }

            // Pokud nemáme table_name z obsahu, zkusit ho získat z config
            if (!$tableName && isset($configMigrations[$migrationName])) {
                $tableName = $configMigrations[$migrationName]['table'] ?? null;
            }

            debug_log("[ModuleManager] getMigrationsFromConfig: Načítám migraci: {$migrationName} (soubor: {$fileName}, tabulka: {$tableName})");

            $migrations[$migrationName] = [
                'name' => $migrationName,
                'file' => $fileName,
                'type' => $type,
                'table_name' => $tableName,
                'sql_content' => $phpContent, // Uložíme PHP obsah místo SQL
                'status' => 'pending',
                'ran_at' => null,
                'error_message' => null,
                'file_path' => $migrationFile,
                'source' => 'config',
                'created_at' => date('Y-m-d H:i:s'),
                'lines_count' => $linesCount
            ];
        }

        debug_log("[ModuleManager] getMigrationsFromConfig: Načteno " . count($migrations) . " migrací z config souborů");
        return $migrations;
    }

    /**
     * Načte migrace z databáze
     */
    private function getMigrationsFromDatabase(string $moduleName): array
    {
        try {
            $repository = $this->entityManager->getRepository(\App\Entities\ModuleMigration::class);

            // Najít modul podle názvu
            $module = $this->getModule($moduleName);
            if (!$module) {
                return [];
            }

            $dbMigrations = $repository->findBy(['module' => $module]);

            $migrations = [];
            foreach ($dbMigrations as $migration) {
                $migrations[$migration->getName()] = [
                    'id' => $migration->getId(),
                    'name' => $migration->getName(),
                    'file' => $migration->getFile(),
                    'type' => $migration->getType(),
                    'table_name' => $migration->getTableName(),
                    'sql_content' => $migration->getSqlContent(),
                    'status' => $migration->getStatus(),
                    'ran_at' => $migration->getRanAt() ? $migration->getRanAt()->format('Y-m-d H:i:s') : null,
                    'error_message' => $migration->getErrorMessage(),
                    'file_path' => $this->config['modules_dir'] . '/' . $moduleName . '/Migrations/' . $migration->getFile(),
                    'source' => 'database',
                    'created_at' => $migration->getCreatedAt()->format('Y-m-d H:i:s'),
                    'lines_count' => $migration->jsonSerialize()['lines_count'],
                    'error' => $migration->getErrorMessage()
                ];
            }

            return $migrations;
        } catch (Exception $e) {
            // Pokud databáze není dostupná, vrátit prázdné pole
            return [];
        }
    }

        /**
     * Sloučí controllery z config.php a databáze
     */
    private function mergeControllers(array $configControllers, array $dbControllers): array
    {
        debug_log("[ModuleManager] mergeControllers: Začínám merge - config: " . count($configControllers) . ", DB: " . count($dbControllers));

        $merged = [];

        // Přidat controllery z config.php
        foreach ($configControllers as $name => $controller) {
            debug_log("[ModuleManager] mergeControllers: Přidávám controller z config: {$name}");
            $merged[$name] = $controller;
        }

        // Přidat nebo aktualizovat controllery z databáze
        foreach ($dbControllers as $name => $controller) {
            if (isset($merged[$name])) {
                // Kontroler existuje v config.php - aktualizovat databázovými daty
                debug_log("[ModuleManager] mergeControllers: Aktualizuji controller z DB: {$name} (merged)");
                $merged[$name] = array_merge($merged[$name], $controller);
                $merged[$name]['source'] = 'merged';
            } else {
                // Kontroler existuje pouze v databázi
                debug_log("[ModuleManager] mergeControllers: Přidávám controller pouze z DB: {$name}");
                $merged[$name] = $controller;
            }
        }

        debug_log("[ModuleManager] mergeControllers: Merge dokončen, celkem: " . count($merged) . " controllerů");
        return array_values($merged);
    }

    /**
     * Sloučí entity z config.php a databáze
     */
    private function mergeEntities(array $configEntities, array $dbEntities): array
    {
        $merged = [];

        // Přidat entity z config.php
        foreach ($configEntities as $name => $entity) {
            $merged[$name] = $entity;
        }

        // Přidat nebo aktualizovat entity z databáze
        foreach ($dbEntities as $name => $entity) {
            if (isset($merged[$name])) {
                // Entity existuje v config.php - aktualizovat databázovými daty
                $merged[$name] = array_merge($merged[$name], $entity);
                $merged[$name]['source'] = 'merged';
            } else {
                // Entity existuje pouze v databázi
                $merged[$name] = $entity;
            }
        }

        return array_values($merged);
    }

    /**
     * Sloučí migrace z config.php a databáze
     */
    private function mergeMigrations(array $configMigrations, array $dbMigrations): array
    {
        $merged = [];

        // Přidat migrace z config.php
        foreach ($configMigrations as $name => $migration) {
            $merged[$name] = $migration;
        }

        // Přidat nebo aktualizovat migrace z databáze
        foreach ($dbMigrations as $name => $migration) {
            if (isset($merged[$name])) {
                // Migrace existuje v config.php - aktualizovat databázovými daty
                $merged[$name] = array_merge($merged[$name], $migration);
                $merged[$name]['source'] = 'merged';
            } else {
                // Migrace existuje pouze v databázi
                $merged[$name] = $migration;
            }
        }

        return array_values($merged);
    }

    /**
     * Vypočítá statistiky migrací
     */
    private function calculateMigrationStatus(array $migrations): array
    {
        $total = count($migrations);
        $ran = 0;
        $pending = 0;
        $failed = 0;

        foreach ($migrations as $migration) {
            switch ($migration['status']) {
                case 'ran':
                    $ran++;
                    break;
                case 'failed':
                    $failed++;
                    break;
                default:
                    $pending++;
                    break;
            }
        }

        return [
            'total' => $total,
            'ran' => $ran,
            'pending' => $pending,
            'failed' => $failed
        ];
    }

    /**
     * Získá všechny konfigurace modulů ze souborového systému
     */
    public function getAllModulesConfig(): array
    {
        $filesystemModules = $this->getAvailableModuleFiles();
        $configs = [];

        foreach ($filesystemModules as $moduleName) {
            $configs[$moduleName] = $this->getModuleConfig($moduleName);
        }

        return $configs;
    }

    /**
     * Získá modul z databáze
     */
    public function getModule(string $moduleName): ?Module
    {
        return $this->entityManager->getRepository(Module::class)
            ->findOneBy(['name' => $moduleName]);
    }

    /**
     * Získá všechny moduly
     */
    public function getAllModules(): array
    {
        return $this->entityManager->getRepository(Module::class)->findAll();
    }

    /**
     * Povolí modul
     */
    public function enableModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        $module->setIsEnabled(true);
        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Zakáže modul
     */
    public function disableModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        $module->setIsEnabled(false);
        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Nainstaluje modul
     */
    public function installModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        try {
            // Spustit install.php migraci
            $installFile = $this->config['modules_dir'] . '/' . $moduleName . '/migrations/install.php';
            if (file_exists($installFile)) {
                require_once $installFile;
                $installClass = 'Install' . ucfirst($moduleName);
                if (class_exists($installClass)) {
                    $installer = new $installClass();
                    $installer->install();
                }
            }

            // Spustit všechny migrace modulu pomocí MigrationCLI
            $this->runModuleMigrations($moduleName);

            $module->setIsInstalled(true);
            $this->entityManager->persist($module);
            $this->entityManager->flush();

            return true;
        } catch (Exception $e) {
            debug_log("Failed to install module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Odinstaluje modul
     */
    public function uninstallModule(string $moduleName): bool
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        try {
            // Spustit uninstall.php migraci
            $uninstallFile = $this->config['modules_dir'] . '/' . $moduleName . '/migrations/uninstall.php';
            if (file_exists($uninstallFile)) {
                require_once $uninstallFile;
                $uninstallClass = 'Uninstall' . ucfirst($moduleName);
                if (class_exists($uninstallClass)) {
                    $uninstaller = new $uninstallClass();
                    $uninstaller->uninstall();
                }
            }

            // Vrátit zpět všechny migrace modulu
            $this->rollbackModuleMigrations($moduleName);

            $module->setIsInstalled(false);
            $module->setIsEnabled(false);
            $this->entityManager->persist($module);
            $this->entityManager->flush();

            return true;
        } catch (Exception $e) {
            debug_log("Failed to uninstall module {$moduleName}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Vytvoří nový modul
     */
    public function createModule(string $name, string $displayName, string $version, ?string $description = null, ?string $author = null): Module
    {
        $module = new Module();
        $module->setName($name)
               ->setDisplayName($displayName)
               ->setVersion($version)
               ->setDescription($description)
               ->setAuthor($author)
               ->setIsEnabled(false)
               ->setIsInstalled(false);

        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return $module;
    }

    /**
     * Aktualizuje nastavení modulu
     */
    public function updateModuleSettings(string $moduleName, array $settings): bool
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        $module->setSettings($settings);
        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Aktualizuje oprávnění modulu
     */
    public function updateModulePermissions(string $moduleName, array $permissions): bool
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return false;
        }

        $module->setPermissions($permissions);
        $this->entityManager->persist($module);
        $this->entityManager->flush();

        return true;
    }

    /**
     * Zkontroluje, zda existuje modul v souborovém systému
     */
    public function moduleExists(string $moduleName): bool
    {
        $modulePath = $this->config['modules_dir'] . '/' . $moduleName;
        return is_dir($modulePath);
    }

    /**
     * Získá seznam dostupných modulů v souborovém systému
     */
    public function getAvailableModuleFiles(): array
    {
        $modulesDir = $this->config['modules_dir'];
        if (!is_dir($modulesDir)) {
            return [];
        }

        $modules = [];
        $dirs = scandir($modulesDir);

        foreach ($dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }

            $modulePath = $modulesDir . '/' . $dir;
            if (is_dir($modulePath)) {
                $configFile = $modulePath . '/config.php';
                if (file_exists($configFile)) {
                    $modules[] = $dir;
                }
            }
        }

        return $modules;
    }

    /**
     * Automaticky proskenuje složku modulů a synchronizuje s databází
     * Tato metoda by měla být volána při startu aplikace
     */
    public function scanAndSyncModules(): array
    {
        $results = [
            'new_modules' => [],
            'updated_modules' => [],
            'removed_modules' => [],
            'errors' => []
        ];

        try {
            // Získat moduly ze souborového systému
            $filesystemModules = $this->getAvailableModuleFiles();

            // Získat moduly z databáze
            $dbModules = $this->getAllModules();
            $dbModuleNames = array_map(fn($module) => $module->getName(), $dbModules);

            // Najít nové moduly (jsou v souborovém systému, ale ne v DB)
            foreach ($filesystemModules as $moduleName) {
                if (!in_array($moduleName, $dbModuleNames)) {
                    $config = $this->getModuleConfig($moduleName);
                    if ($config) {
                        $results['new_modules'][] = [
                            'name' => $moduleName,
                            'config' => $config
                        ];
                    }
                }
            }

            // Najít odstraněné moduly (jsou v DB, ale ne v souborovém systému)
            foreach ($dbModuleNames as $moduleName) {
                if (!in_array($moduleName, $filesystemModules)) {
                    $results['removed_modules'][] = $moduleName;
                }
            }

            // Zkontrolovat aktualizace existujících modulů
            foreach ($dbModules as $module) {
                $moduleName = $module->getName();
                if (in_array($moduleName, $filesystemModules)) {
                    $config = $this->getModuleConfig($moduleName);
                    if ($config) {
                        // Zkontrolovat, zda se změnila verze
                        $currentVersion = $module->getVersion();
                        $newVersion = $config['version'] ?? '1.0.0';

                        if ($currentVersion !== $newVersion) {
                            $results['updated_modules'][] = [
                                'name' => $moduleName,
                                'old_version' => $currentVersion,
                                'new_version' => $newVersion,
                                'config' => $config
                            ];
                        }
                    }
                }
            }

        } catch (Exception $e) {
            $results['errors'][] = 'Chyba při skenování modulů: ' . $e->getMessage();
        }

        return $results;
    }

    /**
     * Automaticky vloží nové moduly do databáze (bez zapnutí/instalace)
     */
    public function autoInsertNewModules(): array
    {
        $results = $this->scanAndSyncModules();
        $inserted = [];

        foreach ($results['new_modules'] as $newModule) {
            try {
                $moduleName = $newModule['name'];
                $config = $newModule['config'];

                // Vytvořit modul v databázi (vypnutý a nenainstalovaný)
                $module = $this->createModule(
                    $moduleName,
                    $config['display_name'] ?? ucfirst($moduleName),
                    $config['version'] ?? '1.0.0',
                    $config['description'] ?? null,
                    $config['author'] ?? null
                );

                if ($module) {
                    // Nastavit modul jako vypnutý a nenainstalovaný
                    $module->setIsEnabled(false);
                    $module->setIsInstalled(false);
                    $this->entityManager->persist($module);
                    $this->entityManager->flush();

                    $inserted[] = $moduleName;
                }
            } catch (Exception $e) {
                $results['errors'][] = "Chyba při vkládání modulu {$moduleName}: " . $e->getMessage();
            }
        }

        return [
            'inserted' => $inserted,
            'errors' => $results['errors']
        ];
    }

    /**
     * Spustí migrace pro konkrétní modul
     */
    private function runModuleMigrations(string $moduleName): void
    {
        $moduleMigrationsDir = $this->config['modules_dir'] . '/' . $moduleName . '/migrations';

        if (!is_dir($moduleMigrationsDir)) {
            return;
        }

        // Najít všechny migrace modulu (kromě install.php a uninstall.php)
        $migrationFiles = glob($moduleMigrationsDir . '/[0-9]*_*.php');

        if (empty($migrationFiles)) {
            return;
        }

        // Seřadit migrace podle názvu souboru
        sort($migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $this->executeModuleMigration($moduleName, $migrationFile);
        }
    }

    /**
     * Vrátí zpět migrace pro konkrétní modul
     */
    private function rollbackModuleMigrations(string $moduleName): void
    {
        $moduleMigrationsDir = $this->config['modules_dir'] . '/' . $moduleName . '/migrations';

        if (!is_dir($moduleMigrationsDir)) {
            return;
        }

        // Najít všechny migrace modulu (kromě install.php a uninstall.php)
        $migrationFiles = glob($moduleMigrationsDir . '/[0-9]*_*.php');

        if (empty($migrationFiles)) {
            return;
        }

        // Seřadit migrace podle názvu souboru (obráceně pro rollback)
        rsort($migrationFiles);

        foreach ($migrationFiles as $migrationFile) {
            $this->rollbackModuleMigration($moduleName, $migrationFile);
        }
    }

    /**
     * Spustí konkrétní migraci modulu
     */
    private function executeModuleMigration(string $moduleName, string $migrationFile): void
    {
        $migrationName = basename($migrationFile, '.php');
        $version = $this->extractVersionFromFilename($migrationName);

        // Zkontrolovat, zda už byla migrace spuštěna
        if ($this->isMigrationExecuted($version)) {
            return;
        }

        require_once $migrationFile;
        $className = $this->getClassNameFromFile($migrationFile);

        if (!class_exists($className)) {
            throw new Exception("Třída {$className} nebyla nalezena v souboru {$migrationFile}");
        }

        $migration = new $className();

        if (method_exists($migration, 'migrate')) {
            $migration->migrate();
        }

        // Zaznamenat spuštěnou migraci
        $this->markMigrationAsExecuted($version, $migrationName);
    }

    /**
     * Vrátí zpět konkrétní migraci modulu
     */
    private function rollbackModuleMigration(string $moduleName, string $migrationFile): void
    {
        $migrationName = basename($migrationFile, '.php');
        $version = $this->extractVersionFromFilename($migrationName);

        // Zkontrolovat, zda byla migrace spuštěna
        if (!$this->isMigrationExecuted($version)) {
            return;
        }

        require_once $migrationFile;
        $className = $this->getClassNameFromFile($migrationFile);

        if (!class_exists($className)) {
            throw new Exception("Třída {$className} nebyla nalezena v souboru {$migrationFile}");
        }

        $migration = new $className();

        if (method_exists($migration, 'rollback')) {
            $migration->rollback();
        }

        // Odstranit záznam o spuštěné migraci
        $this->markMigrationAsNotExecuted($version);
    }

    /**
     * Extrahuje verzi z názvu souboru migrace
     */
    private function extractVersionFromFilename(string $filename): ?string
    {
        if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Získá název třídy z souboru migrace
     */
    private function getClassNameFromFile(string $file): string
    {
        $content = file_get_contents($file);
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        throw new Exception("Nepodařilo se najít název třídy v souboru {$file}");
    }

    /**
     * Zkontroluje, zda byla migrace spuštěna
     */
    private function isMigrationExecuted(string $version): bool
    {
        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare("SELECT COUNT(*) FROM migrations WHERE version = ?");
        $result = $stmt->executeQuery([$version]);
        return $result->fetchOne() > 0;
    }

    /**
     * Označí migraci jako spuštěnou
     */
    private function markMigrationAsExecuted(string $version, string $description): void
    {
        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare("INSERT INTO migrations (version, description, executed_at) VALUES (?, ?, NOW())");
        $stmt->execute([$version, $description]);
    }

    /**
     * Označí migraci jako nespuštěnou
     */
    private function markMigrationAsNotExecuted(string $version): void
    {
        $connection = $this->entityManager->getConnection();
        $stmt = $connection->prepare("DELETE FROM migrations WHERE version = ?");
        $stmt->execute([$version]);
    }
}
