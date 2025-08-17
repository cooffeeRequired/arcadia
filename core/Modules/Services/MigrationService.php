<?php
namespace Core\Modules\Services;

use Core\Modules\Contracts\MigrationRunnerInterface;
use Core\Modules\Contracts\FilesystemScannerInterface;
use Core\Modules\DTO\MigrationInfo;

final readonly class MigrationService
{
    public function __construct(
        private FilesystemScannerInterface $fs,
        private MigrationRunnerInterface   $runner,
    ) {}

    /** @return MigrationInfo[] */
    public function migrations(string $module): array
    {
        return $this->fs->migrationsFromConfig($module);
    }

    /** @return array{total:int,ran:int,pending:int,failed:int} */
    public function status(string $module): array
    {
        $migs = $this->migrations($module);
        $ran = $pending = $failed = 0;
        foreach ($migs as $m) {
            match ($m->status) {
                'ran'    => $ran++,
                'failed' => $failed++,
                default  => $pending++,
            };
        }
        return ['total'=>count($migs),'ran'=>$ran,'pending'=>$pending,'failed'=>$failed];
    }

    public function runAll(string $module): void
    {
        foreach ($this->migrations($module) as $m) {
            $this->runOne($module, $m);
        }
    }

    public function rollbackAll(string $module): void
    {
        $list = $this->migrations($module);
        // opačné pořadí pro rollback
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $this->rollbackOne($module, $list[$i]);
        }
    }

    public function runOne(string $module, MigrationInfo $info): void
    {
        $version = $this->extractVersion($info->name);
        if ($version && $this->runner->isExecuted($version)) return;
        $this->runner->run($module, $info);
        if ($version) $this->runner->markExecuted($version, $info->name);
    }

    public function rollbackOne(string $module, MigrationInfo $info): void
    {
        $version = $this->extractVersion($info->name);
        if ($version && !$this->runner->isExecuted($version)) return;
        $this->runner->rollback($module, $info);
        if ($version) $this->runner->markNotExecuted($version);
    }

    private function extractVersion(string $filename): ?string
    {
        return preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_/', $filename, $m) ? $m[1] : null;
    }
}
