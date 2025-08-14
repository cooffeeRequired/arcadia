<?php

namespace App\Controllers;

use Core\Database\DatabaseLogger;
use Core\Http\Response;
use Core\Render\BaseController;

class DemoController extends BaseController
{
    public function index(): Response\ViewResponse
    {
        // Simulace databázových dotazů pro demonstraci Tracy
        $this->simulateDatabaseQueries();

        $data = [
            'message' => 'Demo stránka s databázovými dotazy',
            'queries_count' => count(DatabaseLogger::$queries),
            'timestamp' => date('Y-m-d H:i:s')
        ];

        return $this->view('demo.index', $data);
    }

    private function simulateDatabaseQueries()
    {
        // Simulace různých typů dotazů
        $queries = [
            'SELECT * FROM customers WHERE active = 1',
            'SELECT COUNT(*) FROM contacts WHERE created_at > ?',
            'INSERT INTO activities (type, description, user_id) VALUES (?, ?, ?)',
            'UPDATE customers SET last_contact = NOW() WHERE id = ?',
            'SELECT c.name, COUNT(o.id) FROM customers c LEFT JOIN orders o ON c.id = o.customer_id GROUP BY c.id'
        ];

        $params = [
            [],
            ['2024-01-01'],
            ['email', 'Kontakt s klientem', 1],
            [5],
            []
        ];

        // Simulace spuštění dotazů
        foreach ($queries as $index => $sql) {
            $logger = new DatabaseLogger();
            $logger->startQuery($sql, $params[$index] ?? []);

            // Simulace času zpracování
            usleep(rand(10000, 100000)); // 10-100ms

            $logger->stopQuery();

            // Přidání počtu řádků
            DatabaseLogger::addRowsToLastQuery(rand(1, 50));
        }
    }
}
