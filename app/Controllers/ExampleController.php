<?php

namespace App\Controllers;

use App\Services\TableUI;
use Core\Http\Response;

class ExampleController
{
    public function showTable(): Response\ViewResponse
    {
        // Příklad dat
        $data = [
            ['id' => 1, 'name' => 'Jan Novák', 'email' => 'jan@example.com', 'phone' => '+420 123 456 789', 'status' => 'Aktivní'],
            ['id' => 2, 'name' => 'Marie Svobodová', 'email' => 'marie@example.com', 'phone' => '+420 987 654 321', 'status' => 'Aktivní'],
            ['id' => 3, 'name' => 'Petr Černý', 'email' => 'petr@example.com', 'phone' => '+420 555 123 456', 'status' => 'Neaktivní'],
            ['id' => 4, 'name' => 'Anna Veselá', 'email' => 'anna@example.com', 'phone' => '+420 777 888 999', 'status' => 'Aktivní'],
            ['id' => 5, 'name' => 'Tomáš Malý', 'email' => 'tomas@example.com', 'phone' => '+420 111 222 333', 'status' => 'Neaktivní'],
        ];

        // Vytvoření tabulky programově s PHP 8.4 funkcionalitami
        $tableUI = new TableUI('customers-table', [
            'headers' => ['ID', 'Jméno', 'Email', 'Telefon', 'Stav'],
            'data' => $data,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 3,
            'title' => 'Seznam zákazníků',
            'icon' => 'fas fa-users',
            'emptyMessage' => 'Žádní zákazníci nebyli nalezeni'
        ]);

        $tableUI->addAction('Upravit', fn($row) => "editCustomer({$row['id']})")
                ->addAction('Smazat', fn($row) => "deleteCustomer({$row['id']})", ['type' => 'danger'])
                ->setSortableColumns([0, 1, 2, 3, 4])
                ->setSearchableColumns([1, 2, 3]);

        return view('example.table', [
            'tableHTML' => $tableUI->render(),
            'tableData' => $data
        ]);
    }
}
