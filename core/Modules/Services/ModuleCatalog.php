<?php
namespace Core\Modules\Services;

use App\Entities\Module;
use Core\Modules\Contracts\ConfigLoaderInterface;
use Core\Modules\Contracts\FilesystemScannerInterface;
use Core\Modules\Contracts\ModuleRepositoryInterface;

final readonly class ModuleCatalog
{
    public function __construct(
        private ModuleRepositoryInterface  $repo,
        private FilesystemScannerInterface $fs,
        private ConfigLoaderInterface      $cfg,
    ) {}

    /** @return array{new_modules:array<int,array{name:string,config:array}>,updated_modules:array<int,array{name:string,old_version:?string,new_version:string,config:array}>,removed_modules:string[],errors:string[]} */
    public function scanAndSync(): array
    {
        $res = ['new_modules'=>[], 'updated_modules'=>[], 'removed_modules'=>[], 'errors'=>[]];

        $fsModules = $this->fs->listModules();
        $dbModules = $this->repo->findAll();
        $dbNames   = array_map(fn(Module $m) => $m->getName(), $dbModules);

        // nové
        foreach ($fsModules as $name) {
            if (!in_array($name, $dbNames, true)) {
                $conf = $this->cfg->loadModuleConfig($name);
                if ($conf) $res['new_modules'][] = ['name'=>$name, 'config'=>$conf];
            }
        }

        // odstraněné
        foreach ($dbNames as $name) {
            if (!in_array($name, $fsModules, true)) {
                $res['removed_modules'][] = $name;
            }
        }

        // změněná verze
        foreach ($dbModules as $m) {
            $name = $m->getName();
            if (in_array($name, $fsModules, true)) {
                $conf = $this->cfg->loadModuleConfig($name);
                if ($conf) {
                    $old = $m->getVersion();
                    $new = (string)($conf['version'] ?? '1.0.0');
                    if ($old !== $new) {
                        $res['updated_modules'][] = [
                            'name'=>$name, 'old_version'=>$old, 'new_version'=>$new, 'config'=>$conf
                        ];
                    }
                }
            }
        }
        return $res;
    }

    /** @return array{inserted:string[],errors:string[]} */
    public function autoInsertNew(): array
    {
        $scan = $this->scanAndSync();
        $inserted = [];
        foreach ($scan['new_modules'] as $nm) {
            $name = $nm['name']; $c = $nm['config'];
            $m = (new Module())
                ->setName($name)
                ->setDisplayName($c['display_name'] ?? ucfirst($name))
                ->setVersion((string)($c['version'] ?? '1.0.0'))
                ->setDescription($c['description'] ?? null)
                ->setAuthor($c['author'] ?? null)
                ->setIsEnabled(false)
                ->setIsInstalled(false);
            $this->repo->persist($m);
            $inserted[] = $name;
        }
        $this->repo->flush();
        return ['inserted'=>$inserted, 'errors'=>$scan['errors']];
    }
}
