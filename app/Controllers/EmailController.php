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

        // Simulace paginace
        $pagination = (object) [
            'from' => 1,
            'to' => count($emails),
            'total' => count($emails),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return $this->view('emails.index', [
            'emails' => $emails,
            'pagination' => $pagination
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
}
