<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use App\Entities\Deal;
use Core\Facades\Container;
use Core\Render\View;
use Core\Routing\Middleware;
use Core\Traits\Controller;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\NotSupported;

class HomeController
{
    use Controller;

    private EntityManager $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    /**
     * @throws NotSupported
     */
    public function index(): string
    {
        $this->middleware('auth');
        // Získání statistik z databáze
        $customersCount = $this->em->getRepository(Customer::class)->count([]);
        $contactsCount = $this->em->getRepository(Contact::class)->count([]);
        $dealsCount = $this->em->getRepository(Deal::class)->count([]);

        // Získání posledních aktivit
        $recentContacts = $this->em->getRepository(Contact::class)->findBy(
            [],
            ['contact_date' => 'DESC'],
            5
        );

        $recentDeals = $this->em->getRepository(Deal::class)->findBy(
            [],
            ['created_at' => 'DESC'],
            5
        );

        // Získání aktivních obchodů
        $activeDeals = $this->em->getRepository(Deal::class)->findBy(
            ['status' => 'active'],
            ['created_at' => 'DESC'],
            10
        );

        // Výpočet celkové hodnoty obchodů
        $totalDealValue = 0;
        foreach ($activeDeals as $deal) {
            if ($deal->getValue()) {
                $totalDealValue += $deal->getValue() * $deal->getProbability();
            }
        }

        // Získání dat pro quick access
        $customers = $this->em->getRepository(Customer::class)->findBy([], ['name' => 'ASC'], 10);
        $contacts = $this->em->getRepository(Contact::class)->findBy([], ['contact_date' => 'DESC'], 10);
        $deals = $this->em->getRepository(Deal::class)->findBy([], ['created_at' => 'DESC'], 10);

        $data = [
            'customersCount' => $customersCount,
            'contactsCount' => $contactsCount,
            'dealsCount' => $dealsCount,
            'totalDealValue' => $totalDealValue,
            'recentContacts' => $recentContacts,
            'recentDeals' => $recentDeals,
            'activeDeals' => $activeDeals,
            'customers' => $customers,
            'contacts' => $contacts,
            'deals' => $deals
        ];

        toast_success('Úspěšná operace!', 25000);
        toast_error('Kritická chyba!', 25000);
        toast_warning('Pozor!', 25000);
        toast_info('Informace Lorem ipsum je označení pro standardní pseudolatinský text užívaný v grafickém designu a navrhování jako', 500000);


        return $this->view('home', $data);
    }
} 