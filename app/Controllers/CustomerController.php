<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use App\Entities\Deal;
use Core\Facades\Container;
use Core\Render\View;
use Doctrine\ORM\EntityManager;

class CustomerController
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

        // Získání zákazníků z databáze
        $customers = $this->em->getRepository(Customer::class)->findAll();
        
        // Simulace paginace (v reálné aplikaci by byla implementována)
        $pagination = (object) [
            'from' => 1,
            'to' => count($customers),
            'total' => count($customers),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return View::render('customers.index', [
            'customers' => $customers,
            'pagination' => $pagination
        ]);
    }

    public function show($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání zákazníka z databáze
        $customer = $this->em->getRepository(Customer::class)->find($id);
        
        if (!$customer) {
            http_response_code(404);
            return View::render('errors.404');
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

        return View::render('customers.show', [
            'customer' => $customer,
            'contacts' => $contacts,
            'deals' => $deals
        ]);
    }

    public function create()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        return View::render('customers.create');
    }

    public function store()
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Zde by byla validace a uložení nového zákazníka
        $customer = new Customer();
        $customer->setName($_POST['name'] ?? '');
        $customer->setEmail($_POST['email'] ?? null);
        $customer->setPhone($_POST['phone'] ?? null);
        $customer->setCompany($_POST['company'] ?? null);
        $customer->setCategory($_POST['category'] ?? 'person');
        $customer->setAddress($_POST['address'] ?? null);
        $customer->setZipCode($_POST['zip_code'] ?? null);
        $customer->setCity($_POST['city'] ?? null);
        $customer->setCountry($_POST['country'] ?? null);
        $customer->setStatus($_POST['status'] ?? 'active');
        $customer->setNotes($_POST['notes'] ?? null);

        $this->em->persist($customer);
        $this->em->flush();

        header('Location: /customers/' . $customer->getId());
        exit;
    }

    public function edit($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání zákazníka z databáze
        $customer = $this->em->getRepository(Customer::class)->find($id);
        
        if (!$customer) {
            http_response_code(404);
            return View::render('errors.404');
        }

        return View::render('customers.edit', [
            'customer' => $customer
        ]);
    }

    public function update($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání zákazníka z databáze
        $customer = $this->em->getRepository(Customer::class)->find($id);
        
        if (!$customer) {
            http_response_code(404);
            return View::render('errors.404');
        }

        // Aktualizace dat zákazníka
        $customer->setName($_POST['name'] ?? '');
        $customer->setEmail($_POST['email'] ?? null);
        $customer->setPhone($_POST['phone'] ?? null);
        $customer->setCompany($_POST['company'] ?? null);
        $customer->setCategory($_POST['category'] ?? 'person');
        $customer->setAddress($_POST['address'] ?? null);
        $customer->setZipCode($_POST['zip_code'] ?? null);
        $customer->setCity($_POST['city'] ?? null);
        $customer->setCountry($_POST['country'] ?? null);
        $customer->setStatus($_POST['status'] ?? 'active');
        $customer->setNotes($_POST['notes'] ?? null);

        $this->em->flush();

        header('Location: /customers/' . $customer->getId());
        exit;
    }

    public function delete($id)
    {
        // Kontrola přihlášení
        \Core\Routing\Middleware::auth();

        // Získání zákazníka z databáze
        $customer = $this->em->getRepository(Customer::class)->find($id);
        
        if (!$customer) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $this->em->remove($customer);
        $this->em->flush();

        header('Location: /customers');
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
            $customer = $this->em->getRepository(Customer::class)->find($id);
            if ($customer) {
                $this->em->remove($customer);
                $deletedCount++;
            }
        }

        $this->em->flush();

        $_SESSION['success'] = "Úspěšně smazáno {$deletedCount} zákazníků.";
        header('Location: /');
        exit;
    }
} 