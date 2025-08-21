<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use App\Entities\Deal;
use Core\Services\TableUI;
use Core\Services\HeaderUI;
use Core\Http\Response;
use Core\Render\BaseController;
use Doctrine\ORM\Exception\NotSupported;

class CustomerController extends BaseController
{
    /**
     * @throws NotSupported
     */
    public function index(): Response\ViewResponse
    {
        $customers = $this->em->getRepository(Customer::class)->findAll();

        // Převod entit na asociativní pole pro tabulku s příklady různých datových typů
        $customersData = array_map(function ($customer) {
            return [
                'id' => $customer->getId(),
                'name' => $customer->getName(),
                'email' => $customer->getEmail() ?? '-',
                'phone' => $customer->getPhone() ?? '-',
                'category' => $customer->getCategory() === 'company' ? 'Společnost' : 'Osoba',
                'company' => $customer->getCompany() ?? '-',
                'status' => $customer->getStatus() === 'active',
                'created_at' => $customer->getCreatedAt() ? $customer->getCreatedAt()->format('Y-m-d H:i:s') : null,
            ];
        }, $customers);

        // Vytvoření moderního headeru s HeaderUI komponentem
        $headerUI = new HeaderUI('customers-header', [
            'title' => 'Seznam zákazníků',
            'icon' => 'fas fa-users',
            'subtitle' => 'Správa zákazníků a kontaktů'
        ]);

        // Přidání statistik
        $headerUI->setStats([
            'total' => [
                'label' => 'Celkem',
                'count' => count($customers) . ' zákazníků',
                'type' => 'blue'
            ],
            'companies' => [
                'label' => 'Společnosti',
                'count' => count(array_filter($customers, fn($c) => $c->getCategory() === 'company')) . ' zákazníků',
                'type' => 'green'
            ],
            'persons' => [
                'label' => 'Osoby',
                'count' => count(array_filter($customers, fn($c) => $c->getCategory() === 'person')) . ' zákazníků',
                'type' => 'yellow'
            ]
        ]);

        // Přidání poslední aktualizace
        $headerUI->setLastUpdate('Poslední aktualizace: ' . date('d.m.Y H:i'));

        // Přidání tlačítek
        $headerUI->addButton(
            'create-customer',
            '<i class="fas fa-plus mr-2"></i>Vytvořit zákazníka',
            function() {
                return "window.location.href='/customers/create'";
            },
            ['type' => 'primary']
        );

        // Vytvoření moderní tabulky s PHP 8.4 funkcionalitami
        $tableUI = new TableUI('customers', [
            'headers' => ['ID', 'Celé jméno', 'Email', 'Telefon', 'Kategorie', 'Stav', 'Vytvořeno'],
            'data' => $customersData,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 10,
            'title' => 'Seznam zákazníků - Ukázka pokročilých funkcí',
            'icon' => 'fas fa-users',
            'emptyMessage' => 'Žádní zákazníci nebyli nalezeni',
            'search_controller' => 'App\\Controllers\\CustomerController',
            'search_method' => 'ajaxSearch'
        ]);

        // Přidání sloupců s ukázkami všech nových funkcí: position, format a convert
        $tableUI
            // ID - zarovnané na střed
            ->addColumn('id', 'ID', ['sortable' => true, 'position' => 'center'])

            // Celé jméno - konverzní funkce spojující křestní jméno a příjmení
            ->addColumn('name', 'Celé jméno', [
                'sortable' => true,
            ])

            // Email - standardní zarovnání vlevo
            ->addColumn('email', 'Email', ['sortable' => true])
            ->addColumn('phone', 'Telefon', ['sortable' => true])
            ->addColumn('category', 'Kategorie')

            // Status - boolean formát s vlastní konverzí, zarovnané na střed
            ->addColumn('status', 'Stav', [
                'sortable' => true,
                'convert' => function($row) {
                    return $row['status'] ?
                        '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktivní</span>' :
                        '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Neaktivní</span>';
                }
            ])
            // Datum vytvoření - formát data
            ->addColumn('created_at', 'Vytvořeno', [
                'sortable' => true,
                'format' => 'date'
            ]);

        /*
         * UKÁZKA VŠECH NOVÝCH FUNKCÍ TableUI:
         *
         * 1. POSITION - zarovnání obsahu sloupce:
         *    'position' => 'left'   (výchozí)
         *    'position' => 'center' (na střed)
         *    'position' => 'right'  (vpravo)
         *
         * 2. FORMAT - automatické formátování dat:
         *    'format' => 'date'     (datum ve formátu cs-CZ)
         *    'format' => 'datetime' (datum a čas)
         *    'format' => 'currency' (měna v CZK)
         *    'format' => 'number'   (číslo s oddělovači)
         *    'format' => 'boolean'  (Ano/Ne pro true/false)
         *
         * 3. CONVERT - vlastní konverzní funkce:
         *    'convert' => function($row) {
         *        return $row['first_name'] . ' ' . $row['last_name'];
         *    }
         *    - Přístup k celému řádku dat
         *    - Možnost kombinovat více polí
         *    - Můžete vracet HTML kód pro styling
         */

        // Uložení dat do session pro AJAX search
        $this->session()->set('table_search_customers', $customersData);

        $tableUI->addSearchPanel('Vyhledat zákazníka...', function ($query) use ($customersData) {
            return array_filter($customersData, function ($row) use ($query) {
                return str_contains(strtolower($row['name']), strtolower($query)) ||
                    str_contains(strtolower($row['email']), strtolower($query)) ||
                    str_contains(strtolower($row['company']), strtolower($query));
            });
        });


        $tableUI->addAction('Zobrazit', fn($row) => "viewCustomer(row)")
                ->addAction('Upravit', fn($row) => "editCustomer(row)")
                ->addAction('Smazat', fn($row) => "deleteCustomer(row)", ['type' => 'danger'])
                ->setSortableColumns([0, 1, 2])
                ->addPerPagePanel([5, 10, 25, 50, 100])
                ->setSearchableColumns([1, 2, 3, 5]);

        // Přidání tlačítek do headeru
        $tableUI->addButtonToHeader(
            'create-customer',
            '<i class="fas fa-plus mr-2"></i>Vytvořit zákazníka',
            'pointer',
            function($params) {
                return "window.location.href = '/customers/create'";
            },
            ['type' => 'success']
        );

        $tableUI->addButtonToHeader(
            'export-csv',
            '<i class="fas fa-download mr-2"></i>Export CSV',
            'pointer',
            function($params) {
                return "exportCustomersToCSV(this.filteredData)";
            },
            ['type' => 'primary']
        );

        // Přidání bulk actions
        $tableUI->addBulkActions([
            'export' => [
                'label' => 'Exportovat vybrané',
                'icon' => 'fas fa-download',
                'type' => 'primary',
                'callback' => function($params) {
                    return "exportSelectedCustomers(this.filteredData)";
                }
            ],
            'delete' => [
                'label' => 'Smazat vybrané',
                'icon' => 'fas fa-trash',
                'type' => 'danger',
                'callback' => function($params) {
                    return "deleteSelectedCustomers(this.filteredData)";
                }
            ],
            'activate' => [
                'label' => 'Aktivovat vybrané',
                'icon' => 'fas fa-check-circle',
                'type' => 'success',
                'callback' => function($params) {
                    return "activateSelectedCustomers(this.filteredData)";
                }
            ],
            'deactivate' => [
                'label' => 'Deaktivovat vybrané',
                'icon' => 'fas fa-times-circle',
                'type' => 'warning',
                'callback' => function($params) {
                    return "deactivateSelectedCustomers(this.filteredData)";
                }
            ]
        ]);

        $pagination = (object) [
            'from' => 1,
            'to' => count($customersData),
            'total' => count($customersData),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return $this->view('customers.index', [
            'customers' => $customers,
            'customersData' => $customersData,
            'headerHTML' => $headerUI->render(),
            'tableHTML' => $tableUI->render(),
            'pagination' => $pagination
        ]);
    }

    public function show($id): Response\ViewResponse
    {
        $customer = $this->em->getRepository(Customer::class)->find($id);

        if (!$customer) {
            return $this->notFound();
        }

        // Získání kontaktů zákazníka
        $contacts = $this->em->getRepository(Contact::class)->findBy(
            ['customer' => $customer],
            ['contact_date' => 'DESC'],
            10
        );

        // Získání obchodů zákazníka
        $deals = $this->em->getRepository(Deal::class)->findBy(
            ['customer' => $customer],
            ['created_at' => 'DESC'],
            10
        );

        return $this->view('customers.show', [
            'customer' => $customer,
            'contacts' => $contacts,
            'deals' => $deals
        ]);
    }

    public function create(): Response\ViewResponse
    {
        return $this->view('customers.create');
    }

    public function store(): void
    {
        $customer = new Customer();
        $customer->setName($this->input('name', ''));
        $customer->setEmail($this->input('email', null));
        $customer->setPhone($this->input('phone', null));
        $customer->setCompany($this->input('company', null));
        $customer->setCategory($this->input('category', 'person'));
        $customer->setAddress($this->input('address', null));
        $customer->setZipCode($this->input('zip_code', null));
        $customer->setCity($this->input('city', null));
        $customer->setCountry($this->input('country', null));
        $customer->setStatus($this->input('status', 'active'));
        $customer->setNotes($this->input('notes', null));

        $this->em->persist($customer);
        $this->em->flush();

        $this->redirect('/customers/' . $customer->getId());
    }

    public function edit($id): Response\ViewResponse
    {
        $customer = $this->em->getRepository(Customer::class)->find($id);

        if (!$customer) {
            return $this->notFound();
        }

        return $this->view('customers.edit', [
            'customer' => $customer
        ]);
    }

    public function update($id): void
    {
        $customer = $this->em->getRepository(Customer::class)->find($id);

        if (!$customer) {
            $this->redirect('/customers');
        }

        // Aktualizace dat zákazníka
        $customer->setName($this->input('name', ''));
        $customer->setEmail($this->input('email', null));
        $customer->setPhone($this->input('phone', null));
        $customer->setCompany($this->input('company', null));
        $customer->setCategory($this->input('category', 'person'));
        $customer->setAddress($this->input('address', null));
        $customer->setZipCode($this->input('zip_code', null));
        $customer->setCity($this->input('city', null));
        $customer->setCountry($this->input('country', null));
        $customer->setStatus($this->input('status', 'active'));
        $customer->setNotes($this->input('notes', null));

        $this->em->flush();

        $this->redirect('/customers/' . $customer->getId());
    }

    public function delete($id): void
    {
        $customer = $this->em->getRepository(Customer::class)->find($id);

        if (!$customer) {
            $this->redirect('/customers');
        }

        $this->em->remove($customer);
        $this->em->flush();

        $this->redirect('/customers');
    }

    public function bulkDelete(): void
    {
        $ids = $this->input('ids', []);

        if (empty($ids)) {
            $this->session('error', 'Nebyly vybrány žádné položky ke smazání.');
            $this->redirect('/customers');
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            $customer = $this->em->getRepository(Customer::class)->find($id);
            if ($customer) {
                $this->em->remove($customer);
                $deletedCount++;
            }
        }

        $this->em->flush();

        $this->session('success', "Úspěšně smazáno {$deletedCount} zákazníků.");
        $this->redirect('/customers');
    }

        /**
     * AJAX metoda pro vyhledávání zákazníků
     */
    public function ajaxSearch(string $query): array
    {
        $customersData = $this->session()->get('table_search_customers', []);

        if (empty($query)) {
            return ['data' => $customersData];
        }

        $filteredData = array_filter($customersData, function ($row) use ($query) {
            return str_contains(strtolower($row['name']), strtolower($query)) ||
                str_contains(strtolower($row['email']), strtolower($query)) ||
                str_contains(strtolower($row['company']), strtolower($query));
        });

        return ['data' => array_values($filteredData)];
    }

    /**
     * AJAX metoda pro řazení zákazníků
     */
    public function ajaxSort(string $key, string $direction, array $data = []): array
    {
        $customersData = $this->session()->get('table_search_customers', []);

        // Pokud máme data z parametru, použijeme je (pro řazení již filtrovaných dat)
        $dataToSort = !empty($data) ? $data : $customersData;

        // Debug informace
        error_log("🔍 Sorting by key: {$key}, direction: {$direction}");
        error_log("📊 First few items before sort: " . json_encode(array_slice($dataToSort, 0, 3)));

        usort($dataToSort, function ($a, $b) use ($key, $direction) {
            $aValue = strval($a[$key] ?? '');
            $bValue = strval($b[$key] ?? '');

            // Debug pro každé porovnání
            error_log("🔍 Comparing: '{$aValue}' vs '{$bValue}' (key: {$key}, direction: {$direction})");

            if ($direction === 'asc') {
                $result = strcasecmp($aValue, $bValue);
                error_log("📊 ASC result: {$result}");
                return $result;
            } else {
                $result = strcasecmp($bValue, $aValue);
                error_log("📊 DESC result: {$result}");
                return $result;
            }
        });

        // Debug informace po řazení
        error_log("📊 First few items after sort: " . json_encode(array_slice($dataToSort, 0, 3)));

        return ['data' => $dataToSort];
    }
}
