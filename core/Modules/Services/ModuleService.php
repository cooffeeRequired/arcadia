<?php
namespace Core\Modules\Services;

use App\Entities\Module;
use Core\Facades\Container;
use Core\Modules\Contracts\ConfigLoaderInterface;
use Core\Modules\Contracts\ModuleRepositoryInterface;

final readonly class ModuleService
{
    public function __construct(
        private ModuleRepositoryInterface $repo,
        private DependencyChecker         $deps,
        private ConfigLoaderInterface     $cfg,
    ) {}

    public function isEnabled(string $name): bool
    {
        return (bool)($this->repo->findOneByName($name)?->isEnabled());
    }

    public function isInstalled(string $name): bool
    {
        return (bool)($this->repo->findOneByName($name)?->isInstalled());
    }

    public function isAvailable(string $name): bool
    {
        $m = $this->repo->findOneByName($name);
        if (!$m || !$m->isEnabled() || !$m->isInstalled()) return false;
        return $this->deps->missing($name) === [];
    }

    public function getSetting(string $name, string $key, mixed $default = null): mixed
    {
        $m = $this->repo->findOneByName($name);
        if (!$m) return $default;
        $settings = (array)$m->getSettings();
        return $settings[$key] ?? $default;
    }

    public function hasPermission(string $name, string $permission, ?string $userRole = null): bool
    {
        if (!$this->isAvailable($name)) return false;

        $m = $this->repo->findOneByName($name);
        $perms = (array)$m?->getPermissions();
        $roles = (array)($perms[$permission] ?? []);

        if ($roles === []) return true;

        if ($userRole === null) {
            $session = Container::get('session');
            $userRole = $session->get('user_role', 'user');
        }
        return in_array($userRole, $roles, true);
    }

    /** @return string[] */
    public function enabledModules(): array
    {
        return array_map(fn(Module $m) => $m->getName(), $this->repo->findEnabled());
    }

    /** @return string[] */
    public function availableModules(): array
    {
        $enabled = $this->repo->findEnabled();
        $out = [];
        foreach ($enabled as $m) {
            if ($m->isInstalled() && $this->deps->missing($m->getName()) === []) {
                $out[] = $m->getName();
            }
        }
        return $out;
    }

    /** @return array<string,string[]> name => missing[] */
    public function modulesWithMissingDependencies(): array
    {
        $out = [];
        foreach ($this->repo->findEnabled() as $m) {
            $missing = $this->deps->missing($m->getName());
            if ($missing) $out[$m->getName()] = $missing;
        }
        return $out;
    }

    /** Vrátí config obohacený o stav z DB */
    public function moduleConfig(string $name): array
    {
        $cfg = $this->cfg->loadModuleConfig($name);

        $m = $this->repo->findOneByName($name);
        if ($m) {
            $cfg['is_enabled']  = $m->isEnabled();
            $cfg['is_installed']= $m->isInstalled();
            $cfg['enabled']     = $m->isEnabled();
            $cfg['installed']   = $m->isInstalled();
            $cfg['is_available']= $this->isAvailable($name);
            $cfg['available']   = $cfg['is_available'];
            $missing = $this->deps->missing($name);
            $cfg['missing_dependencies'] = $missing;
            $cfg['has_missing_dependencies'] = $missing !== [];
        } else {
            $cfg += [
                'is_enabled'=>false,'is_installed'=>false,'enabled'=>false,'installed'=>false,
                'is_available'=>false,'available'=>false,
                'missing_dependencies'=>[],'has_missing_dependencies'=>false,
            ];
        }
        return $cfg;
    }

    public function enable(string $name): bool
    {
        $m = $this->repo->findOneByName($name);
        if (!$m) return false;
        $m->setIsEnabled(true);
        $this->repo->persist($m); $this->repo->flush();
        return true;
    }

    public function disable(string $name): bool
    {
        $m = $this->repo->findOneByName($name);
        if (!$m) return false;
        $m->setIsEnabled(false);
        $this->repo->persist($m); $this->repo->flush();
        return true;
    }
}
