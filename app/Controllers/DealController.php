<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Deal;
use Core\Http\Response;
use Core\Render\BaseController;

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

        $pagination = (object) [
            'from' => 1,
            'to' => count($deals),
            'total' => count($deals),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return $this->view('deals.index', [
            'deals' => $deals,
            'pagination' => $pagination
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
}
