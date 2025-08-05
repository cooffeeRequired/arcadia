<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Deal;
use Core\Facades\Container;
use Core\Render\View;
use Core\Routing\Middleware;
use Doctrine\ORM\EntityManager;

class DealController
{
    private EntityManager $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function index()
    {
        // Kontrola přihlášení
        Middleware::auth();

        // Získání obchodů z databáze s join na zákazníky
        $qb = $this->em->createQueryBuilder();
        $qb->select('d', 'c')
           ->from(Deal::class, 'd')
           ->leftJoin('d.customer', 'c')
           ->orderBy('d.created_at', 'DESC');

        $deals = $qb->getQuery()->getResult();
        
        $pagination = (object) [
            'from' => 1,
            'to' => count($deals),
            'total' => count($deals),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return View::render('deals.index', [
            'deals' => $deals,
            'pagination' => $pagination
        ]);
    }

    public function show($id)
    {
        Middleware::auth();

        $deal = $this->em->getRepository(Deal::class)->find($id);
        
        if (!$deal) {
            http_response_code(404);
            return View::render('errors.404');
        }

        return View::render('deals.show', [
            'deal' => $deal
        ]);
    }

    public function create()
    {
        // Kontrola přihlášení
        Middleware::auth();

        // Získání všech zákazníků pro select
        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('deals.create', [
            'customers' => $customers
        ]);
    }

    public function store()
    {
        // Kontrola přihlášení
        Middleware::auth();

        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);
        
        if (!$customer) {
            http_response_code(400);
            return View::render('errors.400');
        }

        $deal = new Deal();
        $deal->setCustomer($customer);
        $deal->setTitle($_POST['title'] ?? '');
        $deal->setDescription($_POST['description'] ?? null);
        $deal->setValue($_POST['value'] ? (float)$_POST['value'] : null);
        $deal->setStage($_POST['stage'] ?? 'prospecting');
        $deal->setProbability($_POST['probability'] ? (float)$_POST['probability'] : 0.0);
        $deal->setExpectedCloseDate($_POST['expected_close_date'] ? new \DateTime($_POST['expected_close_date']) : null);
        $deal->setStatus($_POST['status'] ?? 'active');

        $this->em->persist($deal);
        $this->em->flush();

        header('Location: /deals/' . $deal->getId());
        exit;
    }

    public function edit($id)
    {
        // Kontrola přihlášení
        Middleware::auth();

        $deal = $this->em->getRepository(Deal::class)->find($id);
        
        if (!$deal) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('deals.edit', [
            'deal' => $deal,
            'customers' => $customers
        ]);
    }

    public function update($id)
    {
        // Kontrola přihlášení
        Middleware::auth();

        $deal = $this->em->getRepository(Deal::class)->find($id);
        
        if (!$deal) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);
        if ($customer) {
            $deal->setCustomer($customer);
        }

        $deal->setTitle($_POST['title'] ?? '');
        $deal->setDescription($_POST['description'] ?? null);
        $deal->setValue($_POST['value'] ? (float)$_POST['value'] : null);
        $deal->setStage($_POST['stage'] ?? 'prospecting');
        $deal->setProbability($_POST['probability'] ? (float)$_POST['probability'] : 0.0);
        $deal->setExpectedCloseDate($_POST['expected_close_date'] ? new \DateTime($_POST['expected_close_date']) : null);
        $deal->setStatus($_POST['status'] ?? 'active');

        $this->em->flush();

        header('Location: /deals/' . $deal->getId());
        exit;
    }

    public function delete($id)
    {
        // Kontrola přihlášení
        Middleware::auth();

        $deal = $this->em->getRepository(Deal::class)->find($id);
        
        if (!$deal) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $this->em->remove($deal);
        $this->em->flush();

        header('Location: /deals');
        exit;
    }

    public function bulkDelete()
    {
        // Kontrola přihlášení
        Middleware::auth();

        $ids = $_POST['ids'] ?? [];
        
        if (empty($ids)) {
            $_SESSION['error'] = 'Nebyly vybrány žádné položky ke smazání.';
            header('Location: /');
            exit;
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

        $_SESSION['success'] = "Úspěšně smazáno {$deletedCount} obchodů.";
        header('Location: /');
        exit;
    }
} 