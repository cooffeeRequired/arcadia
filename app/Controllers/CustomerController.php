<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use App\Entities\Deal;
use Core\Http\Response;
use Core\Render\BaseController;

class CustomerController extends BaseController
{
    public function index(): Response\ViewResponse
    {
        $customers = $this->em->getRepository(Customer::class)->findAll();

        $pagination = (object) [
            'from' => 1,
            'to' => count($customers),
            'total' => count($customers),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return $this->view('customers.index', [
            'customers' => $customers,
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
}
