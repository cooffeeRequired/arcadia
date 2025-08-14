<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use Core\Http\Response;
use Core\Render\BaseController;
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

        $pagination = (object) [
            'from' => 1,
            'to' => count($contacts),
            'total' => count($contacts),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return $this->view('contacts.index', [
            'contacts' => $contacts,
            'pagination' => $pagination
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
}
