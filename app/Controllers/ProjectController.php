<?php

namespace App\Controllers;

use App\Entities\Project;
use App\Entities\ProjectEvent;
use App\Entities\ProjectTimeEntry;
use App\Entities\ProjectFile;
use App\Entities\Customer;
use App\Entities\User;
use Core\Http\Response\ViewResponse;
use Core\Modules\ModuleManager;
use Core\Render\BaseController;
use Doctrine\ORM\EntityRepository;
use Exception;

class ProjectController extends BaseController
{
    private ModuleManager $moduleManager;
    private EntityRepository $projectRepository;
    private EntityRepository $customerRepository;
    private EntityRepository $userRepository;

    public function __construct()
    {
        parent::__construct();

        $this->moduleManager = new ModuleManager();

        // Kontrola, zda je modul projektů povolen
        if (!$this->moduleManager->isAvailable('projects')) {
            $this->redirect('/');
            return;
        }

        $this->projectRepository = $this->em->getRepository(Project::class);
        $this->customerRepository = $this->em->getRepository(Customer::class);
        $this->userRepository = $this->em->getRepository(User::class);
    }

    /**
     * Zobrazí seznam projektů
     */
    public function index(): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'view')) {
            return $this->forbidden('Nemáte oprávnění k zobrazení projektů.');
        }

        $query = $this->query('q', '');
        $status = $this->query('status', '');
        $priority = $this->query('priority', '');
        $customerId = $this->query('customer_id', '');

        $qb = $this->projectRepository->createQueryBuilder('p')
            ->leftJoin('p.customer', 'c')
            ->leftJoin('p.manager', 'm')
            ->select('p', 'c', 'm');

        if ($query) {
            $qb->andWhere('p.name LIKE :query OR p.description LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($status) {
            $qb->andWhere('p.status = :status')
               ->setParameter('status', $status);
        }

        if ($priority) {
            $qb->andWhere('p.priority = :priority')
               ->setParameter('priority', $priority);
        }

        if ($customerId) {
            $qb->andWhere('p.customer = :customerId')
               ->setParameter('customerId', $customerId);
        }

        $qb->orderBy('p.createdAt', 'DESC');

        $projects = $qb->getQuery()->getResult();

        $customers = $this->customerRepository->findAll();

        return $this->view('projects.index', [
            'projects' => $projects,
            'customers' => $customers,
            'filters' => [
                'query' => $query,
                'status' => $status,
                'priority' => $priority,
                'customer_id' => $customerId
            ],
            'moduleManager' => $this->moduleManager
        ]);
    }

    /**
     * Zobrazí formulář pro vytvoření projektu
     */
    public function create()
    {
        if (!$this->moduleManager->hasPermission('projects', 'create')) {
            return $this->forbidden('Nemáte oprávnění k vytvoření projektu.');
        }

        $customers = $this->customerRepository->findAll();
        $users = $this->userRepository->findAll();

        return $this->view('projects.create', [
            'customers' => $customers,
            'users' => $users,
            'moduleManager' => $this->moduleManager
        ]);
    }

    /**
     * Uloží nový projekt
     */
    public function store()
    {
        if (!$this->moduleManager->hasPermission('projects', 'create')) {
            return $this->jsonError('Nemáte oprávnění k vytvoření projektu.', 403);
        }

        try {
            $data = $this->all();

            $customer = $this->customerRepository->find($data['customer_id']);
            if (!$customer) {
                return $this->jsonError('Zákazník nebyl nalezen.');
            }

            $manager = $this->userRepository->find($data['manager_id']);
            if (!$manager) {
                return $this->jsonError('Manažer nebyl nalezen.');
            }

            $project = new Project();
            $project->setName($data['name'])
                   ->setDescription($data['description'] ?? null)
                   ->setStatus($data['status'] ?? 'active')
                   ->setPriority($data['priority'] ?? 'medium')
                   ->setStartDate(new \DateTime($data['start_date']))
                   ->setEndDate($data['end_date'] ? new \DateTime($data['end_date']) : null)
                   ->setBudget($data['budget'] ? (float) $data['budget'] : null)
                   ->setEstimatedHours($data['estimated_hours'] ? (int) $data['estimated_hours'] : null)
                   ->setCustomer($customer)
                   ->setManager($manager);

            // Generování mini page slug
            if ($this->moduleManager->getSetting('projects', 'enable_mini_pages', true)) {
                $slug = $this->generateSlug($data['name']);
                $project->setMiniPageSlug($slug);
            }

            $this->em->persist($project);
            $this->em->flush();

            $this->toastSuccess('Projekt byl úspěšně vytvořen.');

            return $this->jsonSuccess([
                'id' => $project->getId(),
                'redirect' => $this->to('projects.show', ['id' => $project->getId()])
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Chyba při vytváření projektu: ' . $e->getMessage());
        }
    }

    /**
     * Zobrazí detail projektu
     */
    public function show(int $id)
    {
        if (!$this->moduleManager->hasPermission('projects', 'view')) {
            return $this->forbidden('Nemáte oprávnění k zobrazení projektu.');
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            return $this->notFound('Projekt nebyl nalezen.');
        }

        // Načtení souvisejících dat
        $events = $this->em->getRepository(ProjectEvent::class)->findBy(['project' => $project], ['eventDate' => 'ASC']);
        $timeEntries = $this->em->getRepository(ProjectTimeEntry::class)->findBy(['project' => $project], ['startTime' => 'DESC']);
        $files = $this->em->getRepository(ProjectFile::class)->findBy(['project' => $project], ['createdAt' => 'DESC']);

        return $this->view('projects.show', [
            'project' => $project,
            'events' => $events,
            'timeEntries' => $timeEntries,
            'files' => $files,
            'moduleManager' => $this->moduleManager
        ]);
    }

    /**
     * Zobrazí formulář pro editaci projektu
     */
    public function edit(int $id)
    {
        if (!$this->moduleManager->hasPermission('projects', 'edit')) {
            return $this->forbidden('Nemáte oprávnění k editaci projektu.');
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            return $this->notFound('Projekt nebyl nalezen.');
        }

        $customers = $this->customerRepository->findAll();
        $users = $this->userRepository->findAll();

        return $this->view('projects.edit', [
            'project' => $project,
            'customers' => $customers,
            'users' => $users,
            'moduleManager' => $this->moduleManager
        ]);
    }

    /**
     * Aktualizuje projekt
     */
    public function update(int $id)
    {
        if (!$this->moduleManager->hasPermission('projects', 'edit')) {
            return $this->jsonError('Nemáte oprávnění k editaci projektu.', 403);
        }

        try {
            $project = $this->projectRepository->find($id);
            if (!$project) {
                return $this->jsonError('Projekt nebyl nalezen.');
            }

            $data = $this->all();

            $customer = $this->customerRepository->find($data['customer_id']);
            if (!$customer) {
                return $this->jsonError('Zákazník nebyl nalezen.');
            }

            $manager = $this->userRepository->find($data['manager_id']);
            if (!$manager) {
                return $this->jsonError('Manažer nebyl nalezen.');
            }

            $project->setName($data['name'])
                   ->setDescription($data['description'] ?? null)
                   ->setStatus($data['status'] ?? 'active')
                   ->setPriority($data['priority'] ?? 'medium')
                   ->setStartDate(new \DateTime($data['start_date']))
                   ->setEndDate($data['end_date'] ? new \DateTime($data['end_date']) : null)
                   ->setBudget($data['budget'] ? (float) $data['budget'] : null)
                   ->setEstimatedHours($data['estimated_hours'] ? (int) $data['estimated_hours'] : null)
                   ->setCustomer($customer)
                   ->setManager($manager);

            $this->em->flush();

            $this->toastSuccess('Projekt byl úspěšně aktualizován.');

            return $this->jsonSuccess([
                'redirect' => $this->to('projects.show', ['id' => $project->getId()])
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Chyba při aktualizaci projektu: ' . $e->getMessage());
        }
    }

    /**
     * Smaže projekt
     */
    public function delete(int $id)
    {
        if (!$this->moduleManager->hasPermission('projects', 'delete')) {
            return $this->jsonError('Nemáte oprávnění k smazání projektu.', 403);
        }

        try {
            $project = $this->projectRepository->find($id);
            if (!$project) {
                return $this->jsonError('Projekt nebyl nalezen.');
            }

            $this->em->remove($project);
            $this->em->flush();

            $this->toastSuccess('Projekt byl úspěšně smazán.');

            return $this->jsonSuccess([
                'redirect' => $this->to('projects.index')
            ]);

        } catch (Exception $e) {
            return $this->jsonError('Chyba při mazání projektu: ' . $e->getMessage());
        }
    }

    /**
     * Zobrazí mini stránku projektu
     */
    public function miniPage(string $slug)
    {
        $project = $this->projectRepository->findOneBy(['miniPageSlug' => $slug]);
        if (!$project) {
            return $this->notFound('Projekt nebyl nalezen.');
        }

        if (!$project->getMiniPageContent()) {
            return $this->notFound('Mini stránka není k dispozici.');
        }

        return $this->view('projects.mini-page', [
            'project' => $project,
            'moduleManager' => $this->moduleManager
        ]);
    }

    /**
     * API endpoint pro získání dat projektu
     */
    public function apiShow(int $id)
    {
        if (!$this->moduleManager->hasPermission('projects', 'view')) {
            return $this->jsonError('Nemáte oprávnění k zobrazení projektu.', 403);
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            return $this->jsonError('Projekt nebyl nalezen.');
        }

        $data = [
            'id' => $project->getId(),
            'name' => $project->getName(),
            'description' => $project->getDescription(),
            'status' => $project->getStatus(),
            'priority' => $project->getPriority(),
            'start_date' => $project->getStartDate()->format('Y-m-d'),
            'end_date' => $project->getEndDate()?->format('Y-m-d'),
            'budget' => $project->getBudget(),
            'actual_cost' => $project->getActualCost(),
            'estimated_hours' => $project->getEstimatedHours(),
            'actual_hours' => $project->getActualHours(),
            'progress' => $project->getProgress(),
            'budget_utilization' => $project->getBudgetUtilization(),
            'is_overdue' => $project->isOverdue(),
            'customer' => [
                'id' => $project->getCustomer()->getId(),
                'name' => $project->getCustomer()->getName()
            ],
            'manager' => [
                'id' => $project->getManager()->getId(),
                'name' => $project->getManager()->getName()
            ],
            'created_at' => $project->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $project->getUpdatedAt()->format('Y-m-d H:i:s')
        ];

        return $this->jsonSuccess($data);
    }

    /**
     * Generuje slug pro mini stránku
     */
    private function generateSlug(string $name): string
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');

        // Kontrola unikátnosti
        $counter = 1;
        $originalSlug = $slug;

        while ($this->projectRepository->findOneBy(['miniPageSlug' => $slug])) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
