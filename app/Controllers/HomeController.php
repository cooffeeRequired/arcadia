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

        // Bulk actions pro každý typ
        $bulkActions = [
            'customers' => [
                ['url' => '/customers/bulk-delete', 'label' => 'Smazat vybrané'],
                ['url' => '/customers/export', 'label' => 'Exportovat'],
                ['url' => '/customers/merge', 'label' => 'Sloučit duplicity']
            ],
            'contacts' => [
                ['url' => '/contacts/bulk-delete', 'label' => 'Smazat vybrané'],
                ['url' => '/contacts/export', 'label' => 'Exportovat'],
                ['url' => '/contacts/status', 'label' => 'Změnit stav']
            ],
            'deals' => [
                ['url' => '/deals/bulk-delete', 'label' => 'Smazat vybrané'],
                ['url' => '/deals/export', 'label' => 'Exportovat'],
                ['url' => '/deals/status', 'label' => 'Změnit stav']
            ]
        ];

        // Item actions pro každý typ
        $itemActions = [
            'customers' => [
                'edit' => ['url' => '/customers/{id}/edit', 'label' => 'Upravit'],
                'delete' => ['url' => '/customers/{id}/delete', 'label' => 'Smazat'],
                'view' => ['url' => '/customers/{id}', 'label' => 'Zobrazit']
            ],
            'contacts' => [
                'edit' => ['url' => '/contacts/{id}/edit', 'label' => 'Upravit'],
                'delete' => ['url' => '/contacts/{id}/delete', 'label' => 'Smazat'],
                'view' => ['url' => '/contacts/{id}', 'label' => 'Zobrazit']
            ],
            'deals' => [
                'edit' => ['url' => '/deals/{id}/edit', 'label' => 'Upravit'],
                'delete' => ['url' => '/deals/{id}/delete', 'label' => 'Smazat'],
                'view' => ['url' => '/deals/{id}', 'label' => 'Zobrazit']
            ]
        ];

        // Získání aktivit
        $recentActivities = $this->em->getRepository(\App\Entities\Activity::class)
            ->createQueryBuilder('a')
            ->leftJoin('a.customer', 'c')
            ->leftJoin('a.deal', 'd')
            ->leftJoin('a.contact', 'co')
            ->orderBy('a.created_at', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $data = [
            'customersCount' => $customersCount,
            'contactsCount' => $contactsCount,
            'dealsCount' => $dealsCount,
            'totalValue' => $totalDealValue,
            'totalDealValue' => $totalDealValue,
            'recentContacts' => $recentContacts,
            'recentDeals' => $recentDeals,
            'activeDeals' => $activeDeals,
            'customers' => $customers,
            'contacts' => $contacts,
            'deals' => $deals,
            'bulkActions' => $bulkActions,
            'itemActions' => $itemActions,
            'recentActivities' => $recentActivities
        ];
        return $this->view('home', $data);
    }
}
