<?php
namespace Core\Modules\Contracts;

use Core\Modules\DTO\MigrationInfo;

interface MigrationRunnerInterface
{
    /**
     * @param string $moduleName
     * @param MigrationInfo $migration
     * @return void
     */
    public function run(string $moduleName, MigrationInfo $migration): void;

    /**
     * @param string $moduleName
     * @param MigrationInfo $migration
     * @return void
     */
    public function rollback(string $moduleName, MigrationInfo $migration): void;

    /**
     * @param string $version
     * @return bool
     */
    public function isExecuted(string $version): bool;

    public function markExecuted(string $version, string $description): void;
    public function markNotExecuted(string $version): void;
}
