<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Deal;
use App\Entities\Email;
use App\Entities\EmailSignature;
use App\Entities\EmailServer;
use App\Entities\EmailTemplate;
use App\Entities\User;
use Core\Facades\Container;
use Core\Render\View;
use Doctrine\ORM\EntityManager;

class EmailController
{
    private EntityManager $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function index()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání e-mailů z databáze
        $emails = $this->em->getRepository(Email::class)->findBy(
            ['user' => $_SESSION['user_id']],
            ['created_at' => 'DESC'],
            50
        );

        // Simulace paginace
        $pagination = (object) [
            'from' => 1,
            'to' => count($emails),
            'total' => count($emails),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return View::render('emails.index', [
            'emails' => $emails,
            'pagination' => $pagination
        ]);
    }

    public function create()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání dostupných šablon
        $templates = $this->em->getRepository(EmailTemplate::class)->findBy(
            ['user' => $_SESSION['user_id'], 'is_active' => true]
        );

        // Získání dostupných serverů
        $servers = $this->em->getRepository(EmailServer::class)->findBy(
            ['user' => $_SESSION['user_id'], 'is_active' => true]
        );

        // Získání zákazníků pro autocomplete
        $customers = $this->em->getRepository(Customer::class)->findBy(
            ['user' => $_SESSION['user_id']],
            ['name' => 'ASC']
        );

        return View::render('emails.create', [
            'templates' => $templates,
            'servers' => $servers,
            'customers' => $customers
        ]);
    }

    public function store()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $user = $this->em->getRepository(User::class)->find($_SESSION['user_id']);

        $email = new Email();
        $email->setSubject($_POST['subject'] ?? '');
        $email->setBody($_POST['body'] ?? '');
        $email->setFromEmail($_POST['from_email'] ?? '');
        $email->setFromName($_POST['from_name'] ?? '');
        $email->setToEmails($_POST['to_emails'] ?? '');
        $email->setCcEmails($_POST['cc_emails'] ?? null);
        $email->setBccEmails($_POST['bcc_emails'] ?? null);
        $email->setStatus('draft');
        $email->setUser($user);

        // Nastavení zákazníka pokud je vybrán
        if (!empty($_POST['customer_id'])) {
            $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id']);
            if ($customer) {
                $email->setCustomer($customer);
            }
        }

        // Nastavení obchodu pokud je vybrán
        if (!empty($_POST['deal_id'])) {
            $deal = $this->em->getRepository(Deal::class)->find($_POST['deal_id']);
            if ($deal) {
                $email->setDeal($deal);
            }
        }

        // Nastavení šablony pokud je vybrána
        if (!empty($_POST['template_id'])) {
            $template = $this->em->getRepository(EmailTemplate::class)->find($_POST['template_id']);
            if ($template) {
                $email->setTemplate($template);
            }
        }

        // Nastavení serveru pokud je vybrán
        if (!empty($_POST['server_id'])) {
            $server = $this->em->getRepository(EmailServer::class)->find($_POST['server_id']);
            if ($server) {
                $email->setServer($server);
            }
        }

        $this->em->persist($email);
        $this->em->flush();

        header('Location: /emails');
        exit;
    }

    public function show($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            http_response_code(404);
            return View::render('errors.404');
        }

        return View::render('emails.show', [
            'email' => $email
        ]);
    }

    public function edit($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            http_response_code(404);
            return View::render('errors.404');
        }

        // Získání dostupných šablon
        $templates = $this->em->getRepository(EmailTemplate::class)->findBy(
            ['user' => $_SESSION['user_id'], 'is_active' => true]
        );

        // Získání dostupných serverů
        $servers = $this->em->getRepository(EmailServer::class)->findBy(
            ['user' => $_SESSION['user_id'], 'is_active' => true]
        );

        return View::render('emails.edit', [
            'email' => $email,
            'templates' => $templates,
            'servers' => $servers
        ]);
    }

    public function update($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $email->setSubject($_POST['subject'] ?? '');
        $email->setBody($_POST['body'] ?? '');
        $email->setFromEmail($_POST['from_email'] ?? '');
        $email->setFromName($_POST['from_name'] ?? '');
        $email->setToEmails($_POST['to_emails'] ?? '');
        $email->setCcEmails($_POST['cc_emails'] ?? null);
        $email->setBccEmails($_POST['bcc_emails'] ?? null);

        // Nastavení šablony pokud je vybrána
        if (!empty($_POST['template_id'])) {
            $template = $this->em->getRepository(EmailTemplate::class)->find($_POST['template_id']);
            if ($template) {
                $email->setTemplate($template);
            }
        }

        // Nastavení serveru pokud je vybrán
        if (!empty($_POST['server_id'])) {
            $server = $this->em->getRepository(EmailServer::class)->find($_POST['server_id']);
            if ($server) {
                $email->setServer($server);
            }
        }

        $this->em->flush();

        header('Location: /emails/' . $email->getId());
        exit;
    }

    public function delete($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $this->em->remove($email);
        $this->em->flush();

        header('Location: /emails');
        exit;
    }

    public function send($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání e-mailu z databáze
        $email = $this->em->getRepository(Email::class)->find($id);

        if (!$email) {
            http_response_code(404);
            return View::render('errors.404');
        }

        // Zde by byla implementace odeslání e-mailu
        // Pro demonstraci pouze změníme status
        $email->setStatus('sent');
        $email->setSentAt(new \DateTime());

        $this->em->flush();

        header('Location: /emails/' . $email->getId());
        exit;
    }

    public function templates()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání šablon z databáze
        $templates = $this->em->getRepository(EmailTemplate::class)->findBy(
            ['user' => $_SESSION['user_id']],
            ['created_at' => 'DESC']
        );

        return View::render('emails.templates', [
            'templates' => $templates
        ]);
    }

    public function signatures()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání podpisů z databáze
        $signatures = $this->em->getRepository(EmailSignature::class)->findBy(
            ['user' => $_SESSION['user_id']],
            ['created_at' => 'DESC']
        );

        return View::render('emails.signatures', [
            'signatures' => $signatures
        ]);
    }

    public function servers()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání serverů z databáze
        $servers = $this->em->getRepository(EmailSignature::class)->findAll();

        return View::render('emails.servers', [
            'servers' => $servers
        ]);
    }
}
