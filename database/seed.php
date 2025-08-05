<?php

require_once __DIR__ . '/../bootstrap.php';

use Core\Facades\Container;

echo "Spouštím seed data...\n";

try {
    $em = Container::get('doctrine.em');
    $connection = $em->getConnection();

    // Seznam seed souborů
    $seeds = [
        '001_seed_users.sql',
        '002_seed_customers.sql',
        '003_seed_contacts.sql',
        '004_seed_deals.sql'
    ];

    foreach ($seeds as $seed) {
        $file = __DIR__ . '/seeds/' . $seed;
        
        if (file_exists($file)) {
            echo "Spouštím seed: {$seed}\n";
            
            $sql = file_get_contents($file);
            $connection->executeStatement($sql);
            
            echo "✓ Seed {$seed} dokončen\n";
        } else {
            echo "✗ Soubor {$seed} nebyl nalezen\n";
        }
    }

    echo "\nVšechna seed data byla úspěšně načtena!\n";

} catch (Exception $e) {
    echo "Chyba při spouštění seed dat: " . $e->getMessage() . "\n";
    exit(1);
} 