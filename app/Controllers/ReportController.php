<?php

namespace App\Controllers;

use App\Entities\Contact;
use App\Entities\Customer;
use App\Entities\Deal;
use Core\Facades\Container;
use Core\Render\View;
use Doctrine\ORM\EntityManager;

class ReportController
{
    private EntityManager $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function index()
    {
        // Získání základních statistik
        $customersCount = $this->em->getRepository(Customer::class)->count([]);
        $contactsCount = $this->em->getRepository(Contact::class)->count([]);
        $dealsCount = $this->em->getRepository(Deal::class)->count([]);

        // Získání aktivních obchodů
        $activeDeals = $this->em->getRepository(Deal::class)->findBy(['status' => 'active']);
        $activeDealsCount = count($activeDeals);

        // Výpočet celkové hodnoty obchodů
        $totalDealValue = 0;
        $expectedValue = 0;
        foreach ($activeDeals as $deal) {
            if ($deal->getValue()) {
                $totalDealValue += $deal->getValue();
                $expectedValue += $deal->getValue() * ($deal->getProbability() / 100);
            }
        }

        // Získání obchodů podle fáze
        $dealsByStage = [];
        $qb = $this->em->createQueryBuilder();
        $qb->select('d.stage, COUNT(d.id) as count, SUM(d.value) as total_value')
           ->from(Deal::class, 'd')
           ->where('d.status = :status')
           ->setParameter('status', 'active')
           ->groupBy('d.stage');
        
        $stageStats = $qb->getQuery()->getResult();

        // Získání posledních aktivit
        $recentContacts = $this->em->getRepository(Contact::class)->findBy(
            [],
            ['contact_date' => 'DESC'],
            10
        );

        $recentDeals = $this->em->getRepository(Deal::class)->findBy(
            [],
            ['created_at' => 'DESC'],
            10
        );

        return View::render('reports.index', [
            'customersCount' => $customersCount,
            'contactsCount' => $contactsCount,
            'dealsCount' => $dealsCount,
            'activeDealsCount' => $activeDealsCount,
            'totalDealValue' => $totalDealValue,
            'expectedValue' => $expectedValue,
            'stageStats' => $stageStats,
            'recentContacts' => $recentContacts,
            'recentDeals' => $recentDeals
        ]);
    }

    public function customers()
    {
        // Získání zákazníků s počtem kontaktů a obchodů
        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('reports.customers', [
            'customers' => $customers
        ]);
    }

    public function deals()
    {
        // Získání obchodů s detaily
        $deals = $this->em->getRepository(Deal::class)->findAll();

        // Statistiky podle fáze
        $stageStats = [];
        foreach ($deals as $deal) {
            $stage = $deal->getStage();
            if (!isset($stageStats[$stage])) {
                $stageStats[$stage] = ['count' => 0, 'value' => 0];
            }
            $stageStats[$stage]['count']++;
            if ($deal->getValue()) {
                $stageStats[$stage]['value'] += $deal->getValue();
            }
        }

        return View::render('reports.deals', [
            'deals' => $deals,
            'stageStats' => $stageStats
        ]);
    }

    public function contacts()
    {
        // Získání kontaktů s detaily
        $contacts = $this->em->getRepository(Contact::class)->findAll();

        // Statistiky podle typu
        $typeStats = [];
        foreach ($contacts as $contact) {
            $type = $contact->getType();
            if (!isset($typeStats[$type])) {
                $typeStats[$type] = 0;
            }
            $typeStats[$type]++;
        }

        return View::render('reports.contacts', [
            'contacts' => $contacts,
            'typeStats' => $typeStats
        ]);
    }
} 