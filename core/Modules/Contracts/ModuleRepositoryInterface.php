<?php
namespace Core\Modules\Contracts;

use App\Entities\Module;

interface ModuleRepositoryInterface
{
    public function findOneByName(string $name): ?Module;
    /** @return Module[] */
    public function findAll(): array;
    /** @return Module[] */
    public function findEnabled(): array;

    public function persist(Module $module): void;
    public function flush(): void;
}
