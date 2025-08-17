<?php
namespace Core\Modules\Services;

use Core\Modules\Contracts\ConfigLoaderInterface;
use Core\Modules\Contracts\ModuleRepositoryInterface;

final readonly class DependencyChecker
{
    public function __construct(
        private ModuleRepositoryInterface $repo,
        private ConfigLoaderInterface     $cfg,
    ) {}

    /** @return string[] seznam chybějících závislostí modulu */
    public function missing(string $moduleName): array
    {
        $conf = $this->cfg->loadModuleConfig($moduleName);
        $deps = (array)($conf['dependencies'] ?? []);
        if (!$deps) return [];

        $missing = [];
        foreach ($deps as $dep) {
            $m = $this->repo->findOneByName($dep);
            if (!$m || !$m->isEnabled() || !$m->isInstalled()) {
                $missing[] = $dep;
            }
        }
        return $missing;
    }
}
