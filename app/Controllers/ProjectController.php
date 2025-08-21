<?php

namespace App\Controllers;

use App\Entities\Project;
use App\Entities\ProjectEvent;
use App\Entities\ProjectTimeEntry;
use App\Entities\ProjectFile;
use App\Entities\Customer;
use App\Entities\User;
use Core\Http\Response\ViewResponse;
use Core\Facades\Container;
use Core\Render\BaseController;
use Core\Services\TableUI;
use Core\Services\HeaderUI;
use Doctrine\ORM\EntityRepository;
use Exception;

class ProjectController extends BaseController
{
    private EntityRepository $projectRepository;
    private EntityRepository $customerRepository;
    private EntityRepository $userRepository;

    public function __construct()
    {
        parent::__construct();
        $this->projectRepository = $this->em->getRepository(Project::class);
        $this->customerRepository = $this->em->getRepository(Customer::class);
        $this->userRepository = $this->em->getRepository(User::class);
    }

    /**
     * Zobrazí seznam projektů
     */
    public function index(): ViewResponse
    {
        $projects = $this->projectRepository->findAll();

        // Převod na asociativní pole pro TableUI
        $projectsData = array_map(function($project) {
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'customer_name' => $project->getCustomer() ? $project->getCustomer()->getName() : 'N/A',
                'manager_name' => $project->getManager() ? $project->getManager()->getName() : 'N/A',
                'status' => $project->getStatus(),
                'budget' => $project->getBudget() ? number_format($project->getBudget(), 0, ',', ' ') . ' Kč' : 'N/A',
                'start_date' => $project->getStartDate() ? $project->getStartDate()->format('d.m.Y') : 'N/A',
                'end_date' => $project->getEndDate() ? $project->getEndDate()->format('d.m.Y') : 'N/A',
                'description' => $project->getDescription() ? substr($project->getDescription(), 0, 50) . '...' : ''
            ];
        }, $projects);

        // Vytvoření moderního headeru s HeaderUI komponentem
        $headerUI = new HeaderUI('projects-header', [
            'title' => 'Seznam projektů',
            'icon' => 'fas fa-project-diagram',
            'subtitle' => 'Správa projektů a jejich realizace'
        ]);

        // Přidání statistik
        $headerUI->setStats([
            'total' => [
                'label' => 'Celkem',
                'count' => count($projects) . ' projektů',
                'type' => 'blue'
            ],
            'active' => [
                'label' => 'Aktivní',
                'count' => count(array_filter($projects, fn($p) => $p->getStatus() === 'active')) . ' projektů',
                'type' => 'green'
            ],
            'completed' => [
                'label' => 'Dokončené',
                'count' => count(array_filter($projects, fn($p) => $p->getStatus() === 'completed')) . ' projektů',
                'type' => 'purple'
            ],
            'total_budget' => [
                'label' => 'Celkový rozpočet',
                'count' => number_format(array_sum(array_map(fn($p) => $p->getBudget() ?? 0, $projects)), 0, ',', ' ') . ' Kč',
                'type' => 'yellow'
            ]
        ]);

        // Přidání poslední aktualizace
        $headerUI->setLastUpdate('Poslední aktualizace: ' . date('d.m.Y H:i'));

        // Přidání tlačítek
        $headerUI->addButton(
            'create-project',
            '<i class="fas fa-plus mr-2"></i>Nový projekt',
            function() {
                return "window.location.href='/projects/create'";
            },
            ['type' => 'primary']
        );

        // Vytvoření moderní tabulky s TableUI komponentem
        $tableUI = new TableUI('projects', [
            'headers' => ['ID', 'Název', 'Zákazník', 'Manažer', 'Stav', 'Rozpočet', 'Začátek', 'Konec', 'Popis'],
            'data' => $projectsData,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 15,
            'title' => 'Seznam projektů',
            'icon' => 'fas fa-project-diagram',
            'emptyMessage' => 'Žádné projekty nebyly nalezeny',
            'search_controller' => 'App\\Controllers\\ProjectController',
            'search_method' => 'ajaxSearch'
        ]);

        // Přidání sloupců
        $tableUI->addColumn('id', 'ID', ['sortable' => true])
                ->addColumn('name', 'Název', ['sortable' => true])
                ->addColumn('customer_name', 'Zákazník', ['sortable' => true])
                ->addColumn('manager_name', 'Manažer', ['sortable' => true])
                ->addColumn('status', 'Stav', ['sortable' => true, 'position' => 'center'])
                ->addColumn('budget', 'Rozpočet', ['sortable' => true, 'position' => 'right', 'format' => 'currency'])
                ->addColumn('start_date', 'Začátek', ['sortable' => true, 'format' => 'date', 'position' => 'center'])
                ->addColumn('end_date', 'Konec', ['sortable' => true, 'format' => 'date', 'position' => 'center'])
                ->addColumn('description', 'Popis', ['sortable' => false]);

        // Přidání akcí pro řádky
        $tableUI->addAction('Zobrazit', function($params) {
            return "window.location.href='/projects/' + {$params['row']}.id";
        }, ['type' => 'primary'])
        ->addAction('Upravit', function($params) {
            return "window.location.href='/projects/' + {$params['row']}.id + '/edit'";
        }, ['type' => 'default'])
        ->addAction('Smazat', function($params) {
            return "if(confirm('Opravdu smazat projekt?')) window.location.href='/projects/' + {$params['row']}.id + '/delete'";
        }, ['type' => 'danger']);

        // Přidání vyhledávání
        $tableUI->addSearchPanel('Vyhledat projekt...', function() {
            return "searchProjects()";
        });

        // Přidání vlastních tlačítek
        $tableUI->addButtonToHeader(
            'export-projects',
            '<i class="fas fa-download mr-2"></i>Export CSV',
            'pointer',
            function($params) {
                return "exportProjects({$params['filteredData']})";
            },
            ['type' => 'success']
        );

        // Přidání hromadných akcí
        $tableUI->addBulkActions([
            'delete' => [
                'label' => 'Smazat vybrané',
                'icon' => 'fas fa-trash',
                'type' => 'danger',
                'callback' => function($params) {
                    return "if(confirm('Opravdu smazat vybrané projekty?')) deleteSelectedProjects({$params['filteredData']})";
                }
            ],
            'export' => [
                'label' => 'Exportovat vybrané',
                'icon' => 'fas fa-download',
                'type' => 'primary',
                'callback' => function($params) {
                    return "exportSelectedProjects({$params['filteredData']})";
                }
            ]
        ]);

        return $this->view('projects.index', [
            'projects' => $projects,
            'projectsData' => $projectsData,
            'headerHTML' => $headerUI->render(),
            'tableHTML' => $tableUI->render(),
            'title' => 'Seznam projektů'
        ]);
    }


    /**
     * AJAX vyhledávání projektů
     */
    public function ajaxSearch(string $query): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('p', 'c', 'm')
           ->from(Project::class, 'p')
           ->leftJoin('p.customer', 'c')
           ->leftJoin('p.manager', 'm')
           ->where('p.name LIKE :query OR p.status LIKE :query OR c.name LIKE :query OR m.name LIKE :query')
           ->setParameter('query', '%' . $query . '%')
           ->orderBy('p.startDate', 'DESC');

        $projects = $qb->getQuery()->getResult();

        return array_map(function($project) {
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'customer_name' => $project->getCustomer() ? $project->getCustomer()->getName() : 'N/A',
                'manager_name' => $project->getManager() ? $project->getManager()->getName() : 'N/A',
                'status' => $project->getStatus(),
                'budget' => $project->getBudget() ? number_format($project->getBudget(), 0, ',', ' ') . ' Kč' : 'N/A',
                'start_date' => $project->getStartDate() ? $project->getStartDate()->format('d.m.Y') : 'N/A',
                'end_date' => $project->getEndDate() ? $project->getEndDate()->format('d.m.Y') : 'N/A',
                'description' => $project->getDescription() ? substr($project->getDescription(), 0, 50) . '...' : ''
            ];
        }, $projects);
    }
}
