<?php

require_once __DIR__ . '/../bootstrap.php';

use Core\Facades\Container;

echo "Spouštím databázové migrace...\n";

try {
    $em = Container::get('doctrine.em');
    $connection = $em->getConnection();

    // Seznam migračních souborů
    $migrations = [
      // '001_create_users_table.sql',
      // '002_create_customers_table.sql',
      // '003_create_contacts_table.sql',
      // '004_create_deals_table.sql',
        '005_create_activities_table.sql'
    ];

    foreach ($migrations as $migration) {
        $file = __DIR__ . '/migrations/' . $migration;

        if (file_exists($file)) {
            echo "Spouštím migraci: {$migration}\n";

            $sql = file_get_contents($file);
            $connection->executeStatement($sql);

            echo "✓ Migrace {$migration} dokončena\n";
        } else {
            echo "✗ Soubor {$migration} nebyl nalezen\n";
        }
    }

    echo "\nVšechny migrace byly úspěšně spuštěny!\n";

} catch (Exception $e) {
    echo "Chyba při spouštění migrací: " . $e->getMessage() . "\n";
    exit(1);
}
