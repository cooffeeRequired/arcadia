<?php
namespace Core\Modules\Contracts;

interface ConfigLoaderInterface
{
    /** @return array<string,mixed> */
    public function loadModuleConfig(string $moduleName): array;
    public function modulesDir(): string;
}
