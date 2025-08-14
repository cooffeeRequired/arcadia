<?php

namespace Core\Modules;

use App\Entities\Module;
use Core\Facades\Container;
use Doctrine\ORM\EntityManager;
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
     * Získá konfiguraci modulu
     */
    public function getModuleConfig(string $moduleName): array
    {
        $module = $this->getModule($moduleName);
        if (!$module) {
            return [];
        }

        return [
            'enabled' => $module->isEnabled(),
            'installed' => $module->isInstalled(),
            'available' => $this->isAvailable($moduleName),
            'dependencies' => $module->getDependencies() ?? [],
            'missing_dependencies' => $this->checkDependencies($moduleName),
            'settings' => $module->getSettings() ?? [],
            'permissions' => $module->getPermissions() ?? [],
        ];
    }

    /**
     * Získá všechny konfigurace modulů
     */
    public function getAllModulesConfig(): array
    {
        $modules = $this->entityManager->getRepository(Module::class)->findAll();
        $configs = [];

        foreach ($modules as $module) {
            $configs[$module->getName()] = $this->getModuleConfig($module->getName());
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
            error_log("Failed to install module {$moduleName}: " . $e->getMessage());
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
            error_log("Failed to uninstall module {$moduleName}: " . $e->getMessage());
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
