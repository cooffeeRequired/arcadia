<?php
namespace Core\Modules\Contracts;

use Core\Modules\DTO\ControllerInfo;
use Core\Modules\DTO\EntityInfo;
use Core\Modules\DTO\MigrationInfo;

interface FilesystemScannerInterface
{
    /** @return string[] module names present in FS */
    public function listModules(): array;

    /** @return ControllerInfo[] */
    public function controllersFromConfig(string $moduleName): array;

    /** @return EntityInfo[] */
    public function entitiesFromConfig(string $moduleName): array;

    /** @return MigrationInfo[] */
    public function migrationsFromConfig(string $moduleName): array;
}
