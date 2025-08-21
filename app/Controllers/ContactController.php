<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use Core\Http\Response;
use Core\Render\BaseController;
use Core\Services\TableUI;
use Core\Services\HeaderUI;
use Core\Traits\Validation;
use DateTime;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;

class ContactController extends BaseController
{
    use Validation;

    public function index(): Response\ViewResponse
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('c', 'cu')
           ->from(Contact::class, 'c')
           ->leftJoin('c.customer', 'cu')
           ->orderBy('c.contact_date', 'DESC');

        $contacts = $qb->getQuery()->getResult();

        // Převod na asociativní pole pro TableUI
        $contactsData = array_map(function($contact) {
            return [
                'id' => $contact->getId(),
                'customer_name' => $contact->getCustomer() ? $contact->getCustomer()->getName() : 'N/A',
                'type' => $contact->getType(),
                'subject' => $contact->getSubject(),
                'contact_date' => $contact->getContactDate()->format('d.m.Y H:i'),
                'status' => $contact->getStatus(),
                'description' => $contact->getDescription() ? substr($contact->getDescription(), 0, 50) . '...' : ''
            ];
        }, $contacts);

        // Vytvoření moderního headeru s HeaderUI komponentem
        $headerUI = new HeaderUI('contacts-header', [
            'title' => 'Seznam kontaktů',
            'icon' => 'fas fa-phone',
            'subtitle' => 'Správa kontaktů a komunikace se zákazníky'
        ]);

        // Přidání statistik
        $headerUI->setStats([
            'total' => [
                'label' => 'Celkem',
                'count' => count($contacts) . ' kontaktů',
                'type' => 'blue'
            ],
            'completed' => [
                'label' => 'Dokončené',
                'count' => count(array_filter($contacts, fn($c) => $c->getStatus() === 'completed')) . ' kontaktů',
                'type' => 'green'
            ],
            'pending' => [
                'label' => 'Čekající',
                'count' => count(array_filter($contacts, fn($c) => $c->getStatus() === 'pending')) . ' kontaktů',
                'type' => 'yellow'
            ]
        ]);

        // Přidání poslední aktualizace
        $headerUI->setLastUpdate('Poslední aktualizace: ' . date('d.m.Y H:i'));

        // Přidání tlačítek
        $headerUI->addButton(
            'create-contact',
            '<i class="fas fa-plus mr-2"></i>Nový kontakt',
            function() {
                return "window.location.href='/contacts/create'";
            },
            ['type' => 'primary']
        );

        // Vytvoření moderní tabulky s TableUI komponentem
        $tableUI = new TableUI('contacts', [
            'headers' => ['ID', 'Zákazník', 'Typ', 'Předmět', 'Datum', 'Stav', 'Popis'],
            'data' => $contactsData,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 15,
            'title' => 'Seznam kontaktů',
            'icon' => 'fas fa-phone',
            'emptyMessage' => 'Žádné kontakty nebyly nalezeny',
            'search_controller' => 'App\\Controllers\\ContactController',
            'search_method' => 'ajaxSearch'
        ]);

        // Přidání sloupců
        $tableUI->addColumn('id', 'ID', ['sortable' => true])
                ->addColumn('customer_name', 'Zákazník', ['sortable' => true])
                ->addColumn('type', 'Typ', ['sortable' => true, 'position' => 'center'])
                ->addColumn('subject', 'Předmět', ['sortable' => true])
                ->addColumn('contact_date', 'Datum', ['sortable' => true, 'format' => 'datetime', 'position' => 'center'])
                ->addColumn('status', 'Stav', ['sortable' => true, 'position' => 'center'])
                ->addColumn('description', 'Popis', ['sortable' => false]);

        // Přidání akcí pro řádky
        $tableUI->addAction('Zobrazit', function($params) {
            return "window.location.href='/contacts/' + {$params['row']}.id";
        }, ['type' => 'primary'])
        ->addAction('Upravit', function($params) {
            return "window.location.href='/contacts/' + {$params['row']}.id + '/edit'";
        }, ['type' => 'default'])
        ->addAction('Smazat', function($params) {
            return "if(confirm('Opravdu smazat kontakt?')) window.location.href='/contacts/' + {$params['row']}.id + '/delete'";
        }, ['type' => 'danger']);

        // Přidání vyhledávání
        $tableUI->addSearchPanel('Vyhledat kontakt...', function() {
            return "searchContacts()";
        });

        // Přidání vlastních tlačítek
        $tableUI->addButtonToHeader(
            'export-contacts',
            '<i class="fas fa-download mr-2"></i>Export CSV',
            'pointer',
            function($params) {
                return "exportContacts({$params['filteredData']})";
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
                    return "if(confirm('Opravdu smazat vybrané kontakty?')) deleteSelectedContacts({$params['filteredData']})";
                }
            ],
            'export' => [
                'label' => 'Exportovat vybrané',
                'icon' => 'fas fa-download',
                'type' => 'primary',
                'callback' => function($params) {
                    return "exportSelectedContacts({$params['filteredData']})";
                }
            ]
        ]);

        return $this->view('contacts.index', [
            'contacts' => $contacts,
            'contactsData' => $contactsData,
            'headerHTML' => $headerUI->render(),
            'tableHTML' => $tableUI->render(),
            'pagination' => (object) [
                'from' => 1,
                'to' => count($contacts),
                'total' => count($contacts),
                'currentPage' => 1,
                'lastPage' => 1
            ]
        ]);
    }

    /**
     * @throws NotSupported
     */
    public function show($id): Response\ViewResponse
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            return $this->notFound();
        }

        return $this->view('contacts.show', ['contact' => $contact]);
    }

    /**
     * @throws NotSupported
     */
    public function create(): Response\ViewResponse
    {
        $customers = $this->em->getRepository(Customer::class)->findAll();
        return $this->view('contacts.create', ['customers' => $customers]);
    }

    /**
     * @throws NotSupported
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    public function store(): void
    {
        $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id', 0));

        if (!$customer) {
            $this->redirect('/contacts');
        }

        $contact = new Contact();
        $contact->setCustomer($customer);
        $contact->setType($this->input('type', ''));
        $contact->setSubject($this->input('subject', ''));
        $contact->setDescription($this->input('description', null));
        $contact->setContactDate(new DateTime($this->input('contact_date', 'now')));
        $contact->setStatus($this->input('status', 'completed'));

        $this->em->persist($contact);
        $this->em->flush();
        $this->redirect('/contacts/' . $contact->getId());
    }

    public function edit($id): Response\ViewResponse
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            return $this->notFound();
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();
        return $this->view('contacts.edit', ['contact' => $contact, 'customers' => $customers]);
    }

    public function update($id): void
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            $this->redirect('/contacts');
        }

        $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id', 0));
        if ($customer) {
            $contact->setCustomer($customer);
        }

        $contact->setType($this->input('type', ''));
        $contact->setSubject($this->input('subject', ''));
        $contact->setDescription($this->input('description', null));
        $contact->setContactDate(new DateTime($this->input('contact_date', 'now')));
        $contact->setStatus($this->input('status', 'completed'));

        $this->em->flush();

        $this->redirect('/contacts/' . $contact->getId());
    }

    public function delete($id): void
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            $this->redirect('/contacts');
        }

        $this->em->remove($contact);
        $this->em->flush();

        $this->redirect('/contacts');
    }

    public function bulkDelete(): void
    {
        $ids = $this->input('ids', []);

        if (empty($ids)) {
            $this->session('error', 'Nebyly vybrány žádné položky ke smazání.');
            $this->redirect('/contacts');
        }

        $deletedCount = 0;
        foreach ($ids as $id) {
            $contact = $this->em->getRepository(Contact::class)->find($id);
            if ($contact) {
                $this->em->remove($contact);
                $deletedCount++;
            }
        }

        $this->em->flush();

        $this->session('success', "Úspěšně smazáno {$deletedCount} kontaktů.");
        $this->redirect('/contacts');
    }

    /**
     * AJAX vyhledávání kontaktů
     */
    public function ajaxSearch(string $query): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('c', 'cu')
           ->from(Contact::class, 'c')
           ->leftJoin('c.customer', 'cu')
           ->where('c.subject LIKE :query OR c.type LIKE :query OR cu.name LIKE :query')
           ->setParameter('query', '%' . $query . '%')
           ->orderBy('c.contact_date', 'DESC');

        $contacts = $qb->getQuery()->getResult();

        return array_map(function($contact) {
            return [
                'id' => $contact->getId(),
                'customer_name' => $contact->getCustomer() ? $contact->getCustomer()->getName() : 'N/A',
                'type' => $contact->getType(),
                'subject' => $contact->getSubject(),
                'contact_date' => $contact->getContactDate()->format('d.m.Y H:i'),
                'status' => $contact->getStatus(),
                'description' => $contact->getDescription() ? substr($contact->getDescription(), 0, 50) . '...' : ''
            ];
        }, $contacts);
    }
}
