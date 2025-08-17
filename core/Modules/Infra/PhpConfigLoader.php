<?php
namespace Core\Modules\Infra;

use Core\Modules\Contracts\ConfigLoaderInterface;

final readonly class PhpConfigLoader implements ConfigLoaderInterface
{
    public function __construct(private array $appConfig) {}

    public function modulesDir(): string
    {
        return rtrim($this->appConfig['modules_dir'] ?? 'modules', '/');
    }

    public function loadModuleConfig(string $moduleName): array
    {
        $file = $this->modulesDir() . "/{$moduleName}/config.php";
        if (!is_file($file)) {
            return [];
        }
        $cfg = require $file;
        return is_array($cfg) ? $cfg : [];
    }
}
