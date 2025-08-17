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
use Core\Facades\Container;
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

        $this->moduleManager = Container::get(ModuleManager::class, ModuleManager::class);

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
        // Kontrola oprávnění
        if (!$this->moduleManager->hasPermission('projects', 'view')) {
            $this->toastError('Nemáte oprávnění k zobrazení projektů');
            $this->redirect('/');
            return $this->view('home.index', []);
        }

        $projects = $this->projectRepository->findAll();

        $data = [
            'projects' => $projects,
            'title' => 'Seznam projektů'
        ];

        return $this->view('projects.index', $data);
    }

    /**
     * Zobrazí formulář pro vytvoření projektu
     */
    public function create(): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'create')) {
            $this->toastError('Nemáte oprávnění k vytvoření projektu');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        $customers = $this->customerRepository->findAll();
        $users = $this->userRepository->findAll();

        return $this->view('projects.create', [
            'customers' => $customers,
            'users' => $users,
            'title' => 'Nový projekt'
        ]);
    }

    /**
     * Uloží nový projekt
     */
    public function store(): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'create')) {
            $this->toastError('Nemáte oprávnění k vytvoření projektu');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        try {
            $data = $this->request->getJson();

            $project = new Project();
            $project->setName($data['name']);
            $project->setDescription($data['description'] ?? '');
            $project->setStatus($data['status'] ?? 'active');
            $project->setStartDate(new \DateTime($data['start_date']));
            $project->setEndDate(isset($data['end_date']) ? new \DateTime($data['end_date']) : null);
            $project->setBudget($data['budget'] ?? 0);

            // Přiřazení zákazníka
            if (isset($data['customer_id'])) {
                $customer = $this->customerRepository->find($data['customer_id']);
                if ($customer) {
                    $project->setCustomer($customer);
                }
            }

            // Přiřazení manažera
            if (isset($data['manager_id'])) {
                $manager = $this->userRepository->find($data['manager_id']);
                if ($manager) {
                    $project->setManager($manager);
                }
            }

            $this->em->persist($project);
            $this->em->flush();

            $this->toastSuccess('Projekt byl úspěšně vytvořen');
            $this->redirect('/projects/' . $project->getId());

            return $this->view('projects.show', ['project' => $project]);
        } catch (Exception $e) {
            $this->toastError('Chyba při vytváření projektu: ' . $e->getMessage());
            $this->redirect('/projects/create');
            return $this->view('projects.create', []);
        }
    }

    /**
     * Zobrazí detail projektu
     */
    public function show($id): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'view')) {
            $this->toastError('Nemáte oprávnění k zobrazení projektu');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            $this->toastError('Projekt nebyl nalezen');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        return $this->view('projects.show', [
            'project' => $project,
            'title' => 'Detail projektu: ' . $project->getName()
        ]);
    }

    /**
     * Zobrazí formulář pro editaci projektu
     */
    public function edit($id): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'edit')) {
            $this->toastError('Nemáte oprávnění k editaci projektu');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        $project = $this->projectRepository->find($id);
        if (!$project) {
            $this->toastError('Projekt nebyl nalezen');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        $customers = $this->customerRepository->findAll();
        $users = $this->userRepository->findAll();

        return $this->view('projects.edit', [
            'project' => $project,
            'customers' => $customers,
            'users' => $users,
            'title' => 'Editace projektu: ' . $project->getName()
        ]);
    }

    /**
     * Aktualizuje projekt
     */
    public function update($id): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'edit')) {
            $this->toastError('Nemáte oprávnění k editaci projektu');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        try {
            $project = $this->projectRepository->find($id);
            if (!$project) {
                $this->toastError('Projekt nebyl nalezen');
                $this->redirect('/projects');
                return $this->view('projects.index', ['projects' => []]);
            }

            $data = $this->request->getJson();

            $project->setName($data['name']);
            $project->setDescription($data['description'] ?? '');
            $project->setStatus($data['status'] ?? 'active');
            $project->setStartDate(new \DateTime($data['start_date']));
            $project->setEndDate(isset($data['end_date']) ? new \DateTime($data['end_date']) : null);
            $project->setBudget($data['budget'] ?? 0);

            // Přiřazení zákazníka
            if (isset($data['customer_id'])) {
                $customer = $this->customerRepository->find($data['customer_id']);
                if ($customer) {
                    $project->setCustomer($customer);
                }
            }

            // Přiřazení manažera
            if (isset($data['manager_id'])) {
                $manager = $this->userRepository->find($data['manager_id']);
                if ($manager) {
                    $project->setManager($manager);
                }
            }

            $this->em->persist($project);
            $this->em->flush();

            $this->toastSuccess('Projekt byl úspěšně aktualizován');
            $this->redirect('/projects/' . $project->getId());

            return $this->view('projects.show', ['project' => $project]);
        } catch (Exception $e) {
            $this->toastError('Chyba při aktualizaci projektu: ' . $e->getMessage());
            $this->redirect('/projects/' . $id . '/edit');
            return $this->view('projects.edit', []);
        }
    }

    /**
     * Smaže projekt
     */
    public function delete($id): ViewResponse
    {
        if (!$this->moduleManager->hasPermission('projects', 'delete')) {
            $this->toastError('Nemáte oprávnění ke smazání projektu');
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }

        try {
            $project = $this->projectRepository->find($id);
            if (!$project) {
                $this->toastError('Projekt nebyl nalezen');
                $this->redirect('/projects');
                return $this->view('projects.index', ['projects' => []]);
            }

            $this->em->remove($project);
            $this->em->flush();

            $this->toastSuccess('Projekt byl úspěšně smazán');
            $this->redirect('/projects');

            return $this->view('projects.index', ['projects' => []]);
        } catch (Exception $e) {
            $this->toastError('Chyba při mazání projektu: ' . $e->getMessage());
            $this->redirect('/projects');
            return $this->view('projects.index', ['projects' => []]);
        }
    }
}
