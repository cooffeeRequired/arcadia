<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use Core\Facades\Container;
use Core\Render\View;
use Doctrine\ORM\EntityManager;

class ContactController
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

        // Získání kontaktů z databáze s join na zákazníky
        $qb = $this->em->createQueryBuilder();
        $qb->select('c', 'cu')
           ->from(Contact::class, 'c')
           ->leftJoin('c.customer', 'cu')
           ->orderBy('c.contact_date', 'DESC');

        $contacts = $qb->getQuery()->getResult();

        $pagination = (object) [
            'from' => 1,
            'to' => count($contacts),
            'total' => count($contacts),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return View::render('contacts.index', [
            'contacts' => $contacts,
            'pagination' => $pagination
        ]);
    }

    public function show($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            http_response_code(404);
            return View::render('errors.404');
        }

        return View::render('contacts.show', [
            'contact' => $contact
        ]);
    }

    public function create()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání všech zákazníků pro select
        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('contacts.create', [
            'customers' => $customers
        ]);
    }

    public function store()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);

        if (!$customer) {
            http_response_code(400);
            return View::render('errors.400');
        }

        $contact = new Contact();
        $contact->setCustomer($customer);
        $contact->setType($_POST['type'] ?? '');
        $contact->setSubject($_POST['subject'] ?? '');
        $contact->setDescription($_POST['description'] ?? null);
        $contact->setContactDate(new \DateTime($_POST['contact_date'] ?? 'now'));
        $contact->setStatus($_POST['status'] ?? 'completed');

        $this->em->persist($contact);
        $this->em->flush();

        header('Location: /contacts/' . $contact->getId());
        exit;
    }

    public function edit($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('contacts.edit', [
            'contact' => $contact,
            'customers' => $customers
        ]);
    }

    public function update($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);
        if ($customer) {
            $contact->setCustomer($customer);
        }

        $contact->setType($_POST['type'] ?? '');
        $contact->setSubject($_POST['subject'] ?? '');
        $contact->setDescription($_POST['description'] ?? null);
        $contact->setContactDate(new \DateTime($_POST['contact_date'] ?? 'now'));
        $contact->setStatus($_POST['status'] ?? 'completed');

        $this->em->flush();

        header('Location: /contacts/' . $contact->getId());
        exit;
    }

    public function delete($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $this->em->remove($contact);
        $this->em->flush();

        header('Location: /contacts');
        exit;
    }

    public function bulkDelete()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        $ids = $_POST['ids'] ?? [];

        if (empty($ids)) {
            $_SESSION['error'] = 'Nebyly vybrány žádné položky ke smazání.';
            header('Location: /');
            exit;
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

        $_SESSION['success'] = "Úspěšně smazáno {$deletedCount} kontaktů.";
        header('Location: /');
        exit;
    }
}
