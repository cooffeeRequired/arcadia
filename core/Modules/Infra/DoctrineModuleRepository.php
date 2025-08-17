<?php /** @noinspection PhpUnhandledExceptionInspection */

namespace Core\Modules\Infra;

use App\Entities\Module;
use Core\Modules\Contracts\ModuleRepositoryInterface;
use Doctrine\ORM\EntityManager;

final readonly class DoctrineModuleRepository implements ModuleRepositoryInterface
{
    public function __construct(private EntityManager $em) {}

    public function findOneByName(string $name): ?Module
    {
        return $this->em->getRepository(Module::class)->findOneBy(['name' => $name]);
    }

    public function findAll(): array
    {
        return $this->em->getRepository(Module::class)->findAll();
    }

    public function findEnabled(): array
    {
        return $this->em->getRepository(Module::class)
            ->createQueryBuilder('m')
            ->where('m.isEnabled = :e')->setParameter('e', true)
            ->getQuery()->getResult();
    }

    public function persist(Module $module): void { $this->em->persist($module); }
    public function flush(): void { $this->em->flush(); }
}
