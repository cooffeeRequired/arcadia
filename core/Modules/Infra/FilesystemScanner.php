<?php
namespace Core\Modules\Infra;

use Core\Modules\Contracts\FilesystemScannerInterface;
use Core\Modules\DTO\ControllerInfo;
use Core\Modules\DTO\EntityInfo;
use Core\Modules\DTO\MigrationInfo;

final readonly class FilesystemScanner implements FilesystemScannerInterface
{
    public function __construct(private PhpConfigLoader $loader) {}

    public function listModules(): array
    {
        $dir = $this->loader->modulesDir();
        if (!is_dir($dir)) return [];

        return array_values(array_filter(scandir($dir) ?: [], function ($d) use ($dir) {
            if ($d === '.' || $d === '..') return false;
            return is_dir("$dir/$d") && is_file("$dir/$d/config.php");
        }));
    }

    public function controllersFromConfig(string $moduleName): array
    {
        $cfg = $this->loader->loadModuleConfig($moduleName);
        $dir = $this->loader->modulesDir();

        if (!isset($cfg['controllers']) || !is_array($cfg['controllers'])) return [];

        $out = [];
        foreach ($cfg['controllers'] as $name => $c) {
            $path = "$dir/$moduleName/controllers/{$name}Controller.php";
            $content = @file_get_contents($path) ?: '';
            $lines   = substr_count($content, "\n") + 1;

            $out[] = new ControllerInfo(
                name: $name,
                namespace: $c['namespace'] ?? "Modules\\$moduleName\\Controllers",
                extends: $c['extends'] ?? 'Core\\Controllers\\BaseController',
                methods: $c['methods'] ?? [],
                filePath: $path,
                enabled: (bool)($c['enabled'] ?? true),
                source: 'config',
                methodsCount: count($c['methods'] ?? []),
                linesCount: $lines,
            );
        }
        return $out;
    }

    public function entitiesFromConfig(string $moduleName): array
    {
        $cfg = $this->loader->loadModuleConfig($moduleName);
        $dir = $this->loader->modulesDir();

        if (!isset($cfg['entities']) || !is_array($cfg['entities'])) return [];

        $out = [];
        foreach ($cfg['entities'] as $name => $e) {
            $file = "$dir/$moduleName/Models/$name.php";
            $content = @file_get_contents($file) ?: '';
            $lines   = substr_count($content, "\n") + 1;

            $table = $e['table'] ?? strtolower($name) . 's';

            $out[] = new EntityInfo(
                name: $name,
                tableName: $table,
                namespace: $e['namespace'] ?? "Modules\\$moduleName\\Models",
                extends: $e['extends'] ?? 'Core\\Models\\BaseModel',
                properties: $e['properties'] ?? [],
                filePath: $file,
                tableExists: false, // případná kontrola DB zde/později
                source: 'config',
                columnsCount: count($e['properties'] ?? []),
                linesCount: $lines
            );
        }
        return $out;
    }

    public function migrationsFromConfig(string $moduleName): array
    {
        $dir = $this->loader->modulesDir();
        $migDir = "$dir/$moduleName/migrations";
        if (!is_dir($migDir)) return [];

        $files = glob("$migDir/*.php") ?: [];
        $files = array_values(array_filter($files, fn($f) =>
        !in_array(basename($f), ['install.php', 'uninstall.php'], true)
        ));
        sort($files);

        $out = [];
        foreach ($files as $file) {
            $fileName = basename($file);
            $name = pathinfo($fileName, PATHINFO_FILENAME);
            $php = @file_get_contents($file) ?: '';
            $lines = substr_count($php, "\n") + 1;

            $type = 'create_table';
            $table = null;
            if (preg_match('/createTable\([\'"]([^\'"]+)[\'"]/', $php, $m)) {
                $table = $m[1];
            } elseif (preg_match('/create_(\w+)_table/', $fileName, $m)) {
                $table = $m[1];
            }

            $out[] = new MigrationInfo(
                name: $name,
                file: $fileName,
                type: $type,
                tableName: $table,
                phpContent: $php,
                status: 'pending',
                ranAt: null,
                errorMessage: null,
                filePath: $file,
                source: 'config',
                linesCount: $lines
            );
        }
        return $out;
    }
}
