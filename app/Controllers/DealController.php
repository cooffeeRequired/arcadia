<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Deal;
use Core\Http\Response;
use Core\Render\BaseController;
use Core\Services\TableUI;
use Core\Services\HeaderUI;

class DealController extends BaseController
{
    public function index(): Response\ViewResponse
    {
        // Získání obchodů z databáze s join na zákazníky
        $qb = $this->em->createQueryBuilder();
        $qb->select('d', 'c')
           ->from(Deal::class, 'd')
           ->leftJoin('d.customer', 'c')
           ->orderBy('d.created_at', 'DESC');

        $deals = $qb->getQuery()->getResult();

        // Převod na asociativní pole pro TableUI
        $dealsData = array_map(function($deal) {
            return [
                'id' => $deal->getId(),
                'title' => $deal->getTitle(),
                'customer_name' => $deal->getCustomer() ? $deal->getCustomer()->getName() : 'N/A',
                'value' => $deal->getValue() ? number_format($deal->getValue(), 0, ',', ' ') . ' Kč' : 'N/A',
                'stage' => $deal->getStage(),
                'probability' => $deal->getProbability() ? $deal->getProbability() . '%' : '0%',
                'status' => $deal->getStatus(),
                'expected_close_date' => $deal->getExpectedCloseDate() ? $deal->getExpectedCloseDate()->format('d.m.Y') : 'N/A',
                'created_at' => $deal->getCreatedAt()->format('d.m.Y H:i')
            ];
        }, $deals);

        // Vytvoření moderního headeru s HeaderUI komponentem
        $headerUI = new HeaderUI('deals-header', [
            'title' => 'Seznam obchodů',
            'icon' => 'fas fa-handshake',
            'subtitle' => 'Správa obchodních příležitostí a dealů'
        ]);

        // Přidání statistik
        $headerUI->setStats([
            'total' => [
                'label' => 'Celkem',
                'count' => count($deals) . ' obchodů',
                'type' => 'blue'
            ],
            'active' => [
                'label' => 'Aktivní',
                'count' => count(array_filter($deals, fn($d) => $d->getStatus() === 'active')) . ' obchodů',
                'type' => 'green'
            ],
            'total_value' => [
                'label' => 'Celková hodnota',
                'count' => number_format(array_sum(array_map(fn($d) => $d->getValue() ?? 0, $deals)), 0, ',', ' ') . ' Kč',
                'type' => 'purple'
            ]
        ]);

        // Přidání poslední aktualizace
        $headerUI->setLastUpdate('Poslední aktualizace: ' . date('d.m.Y H:i'));

        // Přidání tlačítek
        $headerUI->addButton(
            'create-deal',
            '<i class="fas fa-plus mr-2"></i>Nový obchod',
            function() {
                return "window.location.href='/deals/create'";
            },
            ['type' => 'primary']
        );

        // Vytvoření moderní tabulky s TableUI komponentem
        $tableUI = new TableUI('deals', [
            'headers' => ['ID', 'Název', 'Zákazník', 'Hodnota', 'Fáze', 'Pravděpodobnost', 'Stav', 'Očekávané uzavření', 'Vytvořeno'],
            'data' => $dealsData,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 15,
            'title' => 'Seznam obchodů',
            'icon' => 'fas fa-handshake',
            'emptyMessage' => 'Žádné obchody nebyly nalezeny',
            'search_controller' => 'App\\Controllers\\DealController',
            'search_method' => 'ajaxSearch'
        ]);

        // Přidání sloupců
        $tableUI->addColumn('id', 'ID', ['sortable' => true])
                ->addColumn('title', 'Název', ['sortable' => true])
                ->addColumn('customer_name', 'Zákazník', ['sortable' => true])
                ->addColumn('value', 'Hodnota', ['sortable' => true, 'position' => 'right', 'format' => 'currency'])
                ->addColumn('stage', 'Fáze', ['sortable' => true, 'position' => 'center'])
                ->addColumn('probability', 'Pravděpodobnost', ['sortable' => true, 'position' => 'center'])
                ->addColumn('status', 'Stav', ['sortable' => true, 'position' => 'center'])
                ->addColumn('expected_close_date', 'Očekávané uzavření', ['sortable' => true, 'format' => 'date', 'position' => 'center'])
                ->addColumn('created_at', 'Vytvořeno', ['sortable' => true, 'format' => 'datetime', 'position' => 'center']);

        // Přidání akcí pro řádky
        $tableUI->addAction('Zobrazit', function($params) {
            return "window.location.href='/deals/' + {$params['row']}.id";
        }, ['type' => 'primary'])
        ->addAction('Upravit', function($params) {
            return "window.location.href='/deals/' + {$params['row']}.id + '/edit'";
        }, ['type' => 'default'])
        ->addAction('Smazat', function($params) {
            return "if(confirm('Opravdu smazat obchod?')) window.location.href='/deals/' + {$params['row']}.id + '/delete'";
        }, ['type' => 'danger']);

        // Přidání vyhledávání
        $tableUI->addSearchPanel('Vyhledat obchod...', function() {
            return "searchDeals()";
        });

        // Přidání vlastních tlačítek
        $tableUI->addButtonToHeader(
            'export-deals',
            '<i class="fas fa-download mr-2"></i>Export CSV',
            'pointer',
            function($params) {
                return "exportDeals({$params['filteredData']})";
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
                    return "if(confirm('Opravdu smazat vybrané obchody?')) deleteSelectedDeals({$params['filteredData']})";
                }
            ],
            'export' => [
                'label' => 'Exportovat vybrané',
                'icon' => 'fas fa-download',
                'type' => 'primary',
                'callback' => function($params) {
                    return "exportSelectedDeals({$params['filteredData']})";
                }
            ]
        ]);

        return $this->view('deals.index', [
            'deals' => $deals,
            'dealsData' => $dealsData,
            'headerHTML' => $headerUI->render(),
            'tableHTML' => $tableUI->render(),
            'pagination' => (object) [
                'from' => 1,
                'to' => count($deals),
                'total' => count($deals),
                'currentPage' => 1,
                'lastPage' => 1
            ]
        ]);
    }

    public function show($id): Response\ViewResponse
    {

        $deal = $this->em->getRepository(Deal::class)->find($id);

        if (!$deal) {
            return $this->notFound();
        }

        return $this->view('deals.show', [
            'deal' => $deal
        ]);
    }

    public function create(): Response\ViewResponse
    {
        // Získání všech zákazníků pro select
        $customers = $this->em->getRepository(Customer::class)->findAll();

        return $this->view('deals.create', [
            'customers' => $customers
        ]);
    }

    public function store(): void
    {
        // Kontrola přihlášení

        $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id', 0));

        if (!$customer) {
            $this->redirect('/deals');
        }

        $deal = new Deal();
        $deal->setCustomer($customer);
        $deal->setTitle($this->input('title', ''));
        $deal->setDescription($this->input('description', null));
        $deal->setValue($this->input('value') ? (float)$this->input('value') : null);
        $deal->setStage($this->input('stage', 'prospecting'));
        $deal->setProbability($this->input('probability') ? (float)$this->input('probability') : 0.0);
        $deal->setExpectedCloseDate($this->input('expected_close_date') ? new \DateTime($this->input('expected_close_date')) : null);
        $deal->setStatus($this->input('status', 'active'));

        $this->em->persist($deal);
        $this->em->flush();

        $this->redirect('/deals/' . $deal->getId());
    }

    public function edit($id): Response\ViewResponse
    {


        $deal = $this->em->getRepository(Deal::class)->find($id);

        if (!$deal) {
            return $this->notFound();
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();

        return $this->view('deals.edit', [
            'deal' => $deal,
            'customers' => $customers
        ]);
    }

    public function update($id): void
    {

        $deal = $this->em->getRepository(Deal::class)->find($id);

        if (!$deal) {
            $this->redirect('/deals');
        }

        $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id', 0));
        if ($customer) {
            $deal->setCustomer($customer);
        }

        $deal->setTitle($this->input('title', ''));
        $deal->setDescription($this->input('description', null));
        $deal->setValue($this->input('value') ? (float)$this->input('value') : null);
        $deal->setStage($this->input('stage', 'prospecting'));
        $deal->setProbability($this->input('probability') ? (float)$this->input('probability') : 0.0);
        $deal->setExpectedCloseDate($this->input('expected_close_date') ? new \DateTime($this->input('expected_close_date')) : null);
        $deal->setStatus($this->input('status', 'active'));

        $this->em->flush();

        $this->redirect('/deals/' . $deal->getId());
    }

    public function delete($id): void
    {
        $deal = $this->em->getRepository(Deal::class)->find($id);

        if (!$deal) {
            $this->redirect('/deals');
        }

        $this->em->remove($deal);
        $this->em->flush();

        $this->redirect('/deals');
    }

    public function bulkDelete(): void
    {
        $ids = $this->input('ids', []);

        if (empty($ids)) {
            $this->session('error', 'Nebyly vybrány žádné položky ke smazání.');
            $this->redirect('/deals');
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            $deal = $this->em->getRepository(Deal::class)->find($id);
            if ($deal) {
                $this->em->remove($deal);
                $deletedCount++;
            }
        }

        $this->em->flush();

        $this->session('success', "Úspěšně smazáno {$deletedCount} obchodů.");
        $this->redirect('/deals');
    }

    /**
     * AJAX vyhledávání obchodů
     */
    public function ajaxSearch(string $query): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('d', 'c')
           ->from(Deal::class, 'd')
           ->leftJoin('d.customer', 'c')
           ->where('d.title LIKE :query OR d.stage LIKE :query OR c.name LIKE :query')
           ->setParameter('query', '%' . $query . '%')
           ->orderBy('d.created_at', 'DESC');

        $deals = $qb->getQuery()->getResult();

        return array_map(function($deal) {
            return [
                'id' => $deal->getId(),
                'title' => $deal->getTitle(),
                'customer_name' => $deal->getCustomer() ? $deal->getCustomer()->getName() : 'N/A',
                'value' => $deal->getValue() ? number_format($deal->getValue(), 0, ',', ' ') . ' Kč' : 'N/A',
                'stage' => $deal->getStage(),
                'probability' => $deal->getProbability() ? $deal->getProbability() . '%' : '0%',
                'status' => $deal->getStatus(),
                'expected_close_date' => $deal->getExpectedCloseDate() ? $deal->getExpectedCloseDate()->format('d.m.Y') : 'N/A',
                'created_at' => $deal->getCreatedAt()->format('d.m.Y H:i')
            ];
        }, $deals);
    }
}
