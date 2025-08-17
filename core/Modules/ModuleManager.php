<?php
namespace Core\Modules;

use Core\Modules\Services\ModuleService;
use Core\Modules\Services\ModuleCatalog;
use Core\Modules\Services\MigrationService;

final readonly class ModuleManager
{
    public function __construct(
        private ModuleService    $service,
        private ModuleCatalog    $catalog,
        private MigrationService $migrations,
    ) {}

    // dostupnost / oprávnění / nastavení
    public function isEnabled(string $m): bool
    {
        debug_log("[ModuleManager] isEnabled called for module: {$m}");
        $result = $this->service->isEnabled($m);
        debug_log("[ModuleManager] isEnabled result for {$m}: " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function isInstalled(string $m): bool
    {
        debug_log("[ModuleManager] isInstalled called for module: {$m}");
        $result = $this->service->isInstalled($m);
        debug_log("[ModuleManager] isInstalled result for {$m}: " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function isAvailable(string $m): bool
    {
        debug_log("[ModuleManager] isAvailable called for module: {$m}");
        $result = $this->service->isAvailable($m);
        debug_log("[ModuleManager] isAvailable result for {$m}: " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function getSetting(string $m, string $k, mixed $d=null): mixed
    {
        debug_log("[ModuleManager] getSetting called for module: {$m}, key: {$k}");
        $result = $this->service->getSetting($m, $k, $d);
        debug_log("[ModuleManager] getSetting result for {$m}.{$k}: " . json_encode($result));
        return $result;
    }

    public function hasPermission(string $m, string $p, ?string $r=null): bool
    {
        debug_log("[ModuleManager] hasPermission called for module: {$m}, permission: {$p}, role: " . ($r ?? 'null'));
        $result = $this->service->hasPermission($m, $p, $r);
        debug_log("[ModuleManager] hasPermission result for {$m}.{$p}: " . ($result ? 'true' : 'false'));
        return $result;
    }

    public function enabledModules(): array
    {
        debug_log("[ModuleManager] enabledModules called");
        $result = $this->service->enabledModules();
        debug_log("[ModuleManager] enabledModules result: " . json_encode($result));
        return $result;
    }

    public function availableModules(): array
    {
        debug_log("[ModuleManager] availableModules called");
        $result = $this->service->availableModules();
        debug_log("[ModuleManager] availableModules result: " . json_encode($result));
        return $result;
    }

    public function modulesWithMissingDependencies(): array
    {
        debug_log("[ModuleManager] modulesWithMissingDependencies called");
        $result = $this->service->modulesWithMissingDependencies();
        debug_log("[ModuleManager] modulesWithMissingDependencies result: " . json_encode($result));
        return $result;
    }

    public function moduleConfig(string $m): array
    {
        debug_log("[ModuleManager] moduleConfig called for module: {$m}");
        $result = $this->service->moduleConfig($m);
        debug_log("[ModuleManager] moduleConfig result for {$m}: " . json_encode($result));
        return $result;
    }

    // FS ↔ DB katalog
    public function scanAndSync(): array
    {
        debug_log("[ModuleManager] scanAndSync called");
        $result = $this->catalog->scanAndSync();
        debug_log("[ModuleManager] scanAndSync result: " . json_encode($result));
        return $result;
    }

    public function autoInsertNew(): array
    {
        debug_log("[ModuleManager] autoInsertNew called");
        $result = $this->catalog->autoInsertNew();
        debug_log("[ModuleManager] autoInsertNew result: " . json_encode($result));
        return $result;
    }

    // migrace
    public function runModuleMigrations(string $m): void
    {
        debug_log("[ModuleManager] runModuleMigrations called for module: {$m}");
        $this->migrations->runAll($m);
        debug_log("[ModuleManager] runModuleMigrations completed for module: {$m}");
    }

    public function rollbackModuleMigrations(string $m): void
    {
        debug_log("[ModuleManager] rollbackModuleMigrations called for module: {$m}");
        $this->migrations->rollbackAll($m);
        debug_log("[ModuleManager] rollbackModuleMigrations completed for module: {$m}");
    }

    public function getModuleMigrationStatus(string $m): array
    {
        debug_log("[ModuleManager] getModuleMigrationStatus called for module: {$m}");
        $result = $this->migrations->status($m);
        debug_log("[ModuleManager] getModuleMigrationStatus result for {$m}: " . json_encode($result));
        return $result;
    }
}
