<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use Core\Facades\Container;
use Core\Render\BaseController;
use Core\Render\View;
use Core\Traits\NotificationTrait;
use Core\Traits\Validation;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use JetBrains\PhpStorm\NoReturn;

class ContactController extends BaseController
{
    use Validation, NotificationTrait;


    public function index(): void
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

        $this->renderView('contacts.index', [
            'contacts' => $contacts,
            'pagination' => $pagination
        ]);
    }

    /**
     * @throws NotSupported
     */
    public function show($id): void
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            $this->status(404)->view('errors.404');
        }
        $this->renderView('contacts.show', ['contact' => $contact]);
    }

    /**
     * @throws NotSupported
     */
    public function create(): void
    {
        $customers = $this->em->getRepository(Customer::class)->findAll();
        $this->renderView('contacts.create', ['customers' => $customers]);
    }

    /**
     * @throws NotSupported
     * @throws \DateMalformedStringException
     * @throws ORMException
     */
    #[NoReturn]
    public function store(): void
    {
        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);

        if (!$customer) {
            $this->status(404)->view('errors.404');
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

    public function edit($id): void
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            $this->status(404)->view('errors.404');
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();
        $this->renderView('contacts.edit', ['contact' => $contact, 'customers' => $customers]);
    }

    public #[NoReturn]
    function update($id): void
    {
        $contact = $this->em->getRepository(Contact::class)->find($id);

        if (!$contact) {
            $this->status(404)->view('errors.404');
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

    public function delete($id)
    {
        // Kontrola přihlášení


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
