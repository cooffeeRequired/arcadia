<?php
namespace Core\Modules;

use Core\Modules\Services\ModuleService;
use Core\Modules\Services\ModuleCatalog;
use Core\Modules\Services\MigrationService;

final class ModuleManager
{
    public function __construct(
        private readonly ModuleService $service,
        private readonly ModuleCatalog $catalog,
        private readonly MigrationService $migrations,
    ) {}

    // dostupnost / oprávnění / nastavení
    public function isEnabled(string $m): bool { return $this->service->isEnabled($m); }
    public function isInstalled(string $m): bool { return $this->service->isInstalled($m); }
    public function isAvailable(string $m): bool { return $this->service->isAvailable($m); }
    public function getSetting(string $m, string $k, mixed $d=null): mixed { return $this->service->getSetting($m,$k,$d); }
    public function hasPermission(string $m, string $p, ?string $r=null): bool { return $this->service->hasPermission($m,$p,$r); }
    public function enabledModules(): array { return $this->service->enabledModules(); }
    public function availableModules(): array { return $this->service->availableModules(); }
    public function modulesWithMissingDependencies(): array { return $this->service->modulesWithMissingDependencies(); }
    public function moduleConfig(string $m): array { return $this->service->moduleConfig($m); }

    // FS ↔ DB katalog
    public function scanAndSync(): array { return $this->catalog->scanAndSync(); }
    public function autoInsertNew(): array { return $this->catalog->autoInsertNew(); }

    // migrace
    public function runModuleMigrations(string $m): void { $this->migrations->runAll($m); }
    public function rollbackModuleMigrations(string $m): void { $this->migrations->rollbackAll($m); }
    public function getModuleMigrationStatus(string $m): array { return $this->migrations->status($m); }
}
