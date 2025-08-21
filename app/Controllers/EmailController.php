<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Deal;
use App\Entities\Email;
use App\Entities\EmailSignature;
use App\Entities\EmailServer;
use App\Entities\EmailTemplate;
use App\Entities\User;
use Core\Http\Response;
use Core\Render\BaseController;
use Core\Services\TableUI;
use Core\Services\HeaderUI;

class EmailController extends BaseController
{
    public function index(): Response\ViewResponse
    {
        // Získání e-mailů z databáze
        $emails = $this->em->getRepository(Email::class)->findBy(
            ['user' => $this->session('user_id')],
            ['created_at' => 'DESC'],
            50
        );

        // Převod na asociativní pole pro TableUI
        $emailsData = array_map(function($email) {
            return [
                'id' => $email->getId(),
                'subject' => $email->getSubject(),
                'from_email' => $email->getFromEmail(),
                'from_name' => $email->getFromName(),
                'to_emails' => $email->getToEmails(),
                'status' => $email->getStatus(),
                'customer_name' => $email->getCustomer() ? $email->getCustomer()->getName() : 'N/A',
                'deal_title' => $email->getDeal() ? $email->getDeal()->getTitle() : 'N/A',
                'created_at' => $email->getCreatedAt()->format('d.m.Y H:i'),
                'sent_at' => $email->getSentAt() ? $email->getSentAt()->format('d.m.Y H:i') : 'N/A'
            ];
        }, $emails);

        // Vytvoření moderního headeru s HeaderUI komponentem
        $headerUI = new HeaderUI('emails-header', [
            'title' => 'Seznam e-mailů',
            'icon' => 'fas fa-envelope',
            'subtitle' => 'Správa e-mailové komunikace'
        ]);

        // Přidání statistik
        $headerUI->setStats([
            'total' => [
                'label' => 'Celkem',
                'count' => count($emails) . ' e-mailů',
                'type' => 'blue'
            ],
            'sent' => [
                'label' => 'Odeslané',
                'count' => count(array_filter($emails, fn($e) => $e->getStatus() === 'sent')) . ' e-mailů',
                'type' => 'green'
            ],
            'draft' => [
                'label' => 'Koncepty',
                'count' => count(array_filter($emails, fn($e) => $e->getStatus() === 'draft')) . ' e-mailů',
                'type' => 'yellow'
            ],
            'failed' => [
                'label' => 'Chyby',
                'count' => count(array_filter($emails, fn($e) => $e->getStatus() === 'failed')) . ' e-mailů',
                'type' => 'red'
            ]
        ]);

        // Přidání poslední aktualizace
        $headerUI->setLastUpdate('Poslední aktualizace: ' . date('d.m.Y H:i'));

        // Přidání tlačítek
        $headerUI->addButton(
            'create-email',
            '<i class="fas fa-plus mr-2"></i>Nový e-mail',
            function() {
                return "window.location.href='/emails/create'";
            },
            ['type' => 'primary']
        );

        // Vytvoření moderní tabulky s TableUI komponentem
        $tableUI = new TableUI('emails', [
            'headers' => ['ID', 'Předmět', 'Od', 'Komu', 'Stav', 'Zákazník', 'Obchod', 'Vytvořeno', 'Odesláno'],
            'data' => $emailsData,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 15,
            'title' => 'Seznam e-mailů',
            'icon' => 'fas fa-envelope',
            'emptyMessage' => 'Žádné e-maily nebyly nalezeny',
            'search_controller' => 'App\\Controllers\\EmailController',
            'search_method' => 'ajaxSearch'
        ]);

        // Přidání sloupců
        $tableUI->addColumn('id', 'ID', ['sortable' => true])
                ->addColumn('subject', 'Předmět', ['sortable' => true])
                ->addColumn('from_email', 'Od', ['sortable' => true])
                ->addColumn('to_emails', 'Komu', ['sortable' => true])
                ->addColumn('status', 'Stav', ['sortable' => true, 'position' => 'center'])
                ->addColumn('customer_name', 'Zákazník', ['sortable' => true])
                ->addColumn('deal_title', 'Obchod', ['sortable' => true])
                ->addColumn('created_at', 'Vytvořeno', ['sortable' => true, 'format' => 'datetime', 'position' => 'center'])
                ->addColumn('sent_at', 'Odesláno', ['sortable' => true, 'format' => 'datetime', 'position' => 'center']);

        // Přidání akcí pro řádky
        $tableUI->addAction('Zobrazit', function($params) {
            return "window.location.href='/emails/' + {$params['row']}.id";
        }, ['type' => 'primary'])
        ->addAction('Upravit', function($params) {
            return "window.location.href='/emails/' + {$params['row']}.id + '/edit'";
        }, ['type' => 'default'])
        ->addAction('Odeslat', function($params) {
            return "if(confirm('Opravdu odeslat e-mail?')) window.location.href='/emails/' + {$params['row']}.id + '/send'";
        }, ['type' => 'success'])
        ->addAction('Smazat', function($params) {
            return "if(confirm('Opravdu smazat e-mail?')) window.location.href='/emails/' + {$params['row']}.id + '/delete'";
        }, ['type' => 'danger']);

        // Přidání vyhledávání
        $tableUI->addSearchPanel('Vyhledat e-mail...', function() {
            return "searchEmails()";
        });

        // Přidání vlastních tlačítek
        $tableUI->addButtonToHeader(
            'export-emails',
            '<i class="fas fa-download mr-2"></i>Export CSV',
            'pointer',
            function($params) {
                return "exportEmails({$params['filteredData']})";
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
                    return "if(confirm('Opravdu smazat vybrané e-maily?')) deleteSelectedEmails({$params['filteredData']})";
                }
            ],
            'send' => [
                'label' => 'Odeslat vybrané',
                'icon' => 'fas fa-paper-plane',
                'type' => 'success',
                'callback' => function($params) {
                    return "if(confirm('Opravdu odeslat vybrané e-maily?')) sendSelectedEmails({$params['filteredData']})";
                }
            ],
            'export' => [
                'label' => 'Exportovat vybrané',
                'icon' => 'fas fa-download',
                'type' => 'primary',
                'callback' => function($params) {
                    return "exportSelectedEmails({$params['filteredData']})";
                }
            ]
        ]);

        return $this->view('emails.index', [
            'emails' => $emails,
            'emailsData' => $emailsData,
            'headerHTML' => $headerUI->render(),
            'tableHTML' => $tableUI->render(),
            'pagination' => (object) [
                'from' => 1,
                'to' => count($emails),
                'total' => count($emails),
                'currentPage' => 1,
                'lastPage' => 1
            ]
        ]);
    }

    public function create(): Response\ViewResponse
    {
        // Získání dostupných šablon
        $templates = $this->em->getRepository(EmailTemplate::class)->findBy(
            ['user' => $this->session('user_id'), 'is_active' => true]
        );

        // Získání dostupných serverů
        $servers = $this->em->getRepository(EmailServer::class)->findBy(
            ['user' => $this->session('user_id'), 'is_active' => true]
        );

        // Získání zákazníků pro autocomplete
        $customers = $this->em->getRepository(Customer::class)->findBy(
            ['user' => $this->session('user_id')],
            ['name' => 'ASC']
        );

        return $this->view('emails.create', [
            'templates' => $templates,
            'servers' => $servers,
            'customers' => $customers
        ]);
    }

    public function store(): void
    {
        $user = $this->em->getRepository(User::class)->find($this->session('user_id'));

        $email = new Email();
        $email->setSubject($this->input('subject', ''));
        $email->setBody($this->input('body', ''));
        $email->setFromEmail($this->input('from_email', ''));
        $email->setFromName($this->input('from_name', ''));
        $email->setToEmails($this->input('to_emails', ''));
        $email->setCcEmails($this->input('cc_emails', null));
        $email->setBccEmails($this->input('bcc_emails', null));
        $email->setStatus('draft');
        $email->setUser($user);

        // Nastavení zákazníka pokud je vybrán
        if ($this->has('customer_id')) {
            $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id'));
            if ($customer) {
                $email->setCustomer($customer);
            }
        }

        // Nastavení obchodu pokud je vybrán
        if ($this->has('deal_id')) {
            $deal = $this->em->getRepository(Deal::class)->find($this->input('deal_id'));
            if ($deal) {
                $email->setDeal($deal);
            }
        }

        // Nastavení šablony pokud je vybrána
        if ($this->has('template_id')) {
            $template = $this->em->getRepository(EmailTemplate::class)->find($this->input('template_id'));
            if ($template) {
                $email->setTemplate($template);
            }
        }

        // Nastavení serveru pokud je vybrán
        if ($this->has('server_id')) {
            $server = $this->em->getRepository(EmailServer::class)->find($this->input('server_id'));
            if ($server) {
                $email->setServer($server);
            }
        }

        $this->em->persist($email);
        $this->em->flush();

        $this->redirect('/emails');
    }

    public function show($id): Response\ViewResponse
    {
        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            return $this->notFound();
        }

        return $this->view('emails.show', [
            'email' => $email
        ]);
    }

    public function edit($id): Response\ViewResponse
    {
        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            return $this->notFound();
        }

        // Získání dostupných šablon
        $templates = $this->em->getRepository(EmailTemplate::class)->findBy(
            ['user' => $this->session('user_id'), 'is_active' => true]
        );

        // Získání dostupných serverů
        $servers = $this->em->getRepository(EmailServer::class)->findBy(
            ['user' => $this->session('user_id'), 'is_active' => true]
        );

        return $this->view('emails.edit', [
            'email' => $email,
            'templates' => $templates,
            'servers' => $servers
        ]);
    }

    public function update($id): void
    {
        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            $this->redirect('/emails');
        }

        $email->setSubject($this->input('subject', ''));
        $email->setBody($this->input('body', ''));
        $email->setFromEmail($this->input('from_email', ''));
        $email->setFromName($this->input('from_name', ''));
        $email->setToEmails($this->input('to_emails', ''));
        $email->setCcEmails($this->input('cc_emails', null));
        $email->setBccEmails($this->input('bcc_emails', null));

        // Nastavení šablony pokud je vybrána
        if ($this->has('template_id')) {
            $template = $this->em->getRepository(EmailTemplate::class)->find($this->input('template_id'));
            if ($template) {
                $email->setTemplate($template);
            }
        }

        // Nastavení serveru pokud je vybrán
        if ($this->has('server_id')) {
            $server = $this->em->getRepository(EmailServer::class)->find($this->input('server_id'));
            if ($server) {
                $email->setServer($server);
            }
        }

        $this->em->flush();

        $this->redirect('/emails/' . $email->getId());
    }

    public function delete($id): void
    {
        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            $this->redirect('/emails');
        }

        $this->em->remove($email);
        $this->em->flush();

        $this->redirect('/emails');
    }

    public function send($id): void
    {
        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            $this->redirect('/emails');
        }

        // Zde by byla implementace odeslání e-mailu
        // Pro demonstraci pouze změníme status
        $email->setStatus('sent');
        $email->setSentAt(new \DateTime());

        $this->em->flush();

        $this->redirect('/emails/' . $email->getId());
    }

    public function templates(): Response\ViewResponse
    {
        // Získání šablon z databáze
        $templates = $this->em->getRepository(EmailTemplate::class)->findBy(
            ['user' => $this->session('user_id')],
            ['created_at' => 'DESC']
        );

        return $this->view('emails.templates', [
            'templates' => $templates
        ]);
    }

    public function signatures(): Response\ViewResponse
    {
        // Získání podpisů z databáze
        $signatures = $this->em->getRepository(EmailSignature::class)->findBy(
            ['user' => $this->session('user_id')],
            ['created_at' => 'DESC']
        );

        return $this->view('emails.signatures', [
            'signatures' => $signatures
        ]);
    }

    public function servers(): Response\ViewResponse
    {
        // Získání serverů z databáze
        $servers = $this->em->getRepository(EmailSignature::class)->findAll();

        return $this->view('emails.servers', [
            'servers' => $servers
        ]);
    }

    /**
     * AJAX vyhledávání e-mailů
     */
    public function ajaxSearch(string $query): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('e', 'c', 'd')
           ->from(Email::class, 'e')
           ->leftJoin('e.customer', 'c')
           ->leftJoin('e.deal', 'd')
           ->where('e.subject LIKE :query OR e.fromEmail LIKE :query OR e.toEmails LIKE :query OR c.name LIKE :query OR d.title LIKE :query')
           ->setParameter('query', '%' . $query . '%')
           ->orderBy('e.createdAt', 'DESC');

        $emails = $qb->getQuery()->getResult();

        return array_map(function($email) {
            return [
                'id' => $email->getId(),
                'subject' => $email->getSubject(),
                'from_email' => $email->getFromEmail(),
                'from_name' => $email->getFromName(),
                'to_emails' => $email->getToEmails(),
                'status' => $email->getStatus(),
                'customer_name' => $email->getCustomer() ? $email->getCustomer()->getName() : 'N/A',
                'deal_title' => $email->getDeal() ? $email->getDeal()->getTitle() : 'N/A',
                'created_at' => $email->getCreatedAt()->format('d.m.Y H:i'),
                'sent_at' => $email->getSentAt() ? $email->getSentAt()->format('d.m.Y H:i') : 'N/A'
            ];
        }, $emails);
    }
}
