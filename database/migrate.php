<?php

require_once __DIR__ . '/../bootstrap.php';

use Core\Facades\Container;

echo "Spouštím databázové migrace...\n";

try {
    $em = Container::get('doctrine.em');
    $connection = $em->getConnection();

    // Automatické načtení všech migračních souborů
    $migrationsDir = __DIR__ . '/migrations';
    $migrations = [];

    if (is_dir($migrationsDir)) {
        $files = scandir($migrationsDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $migrations[] = $file;
            }
        }

        // Seřazení migrací podle názvu (pro správné pořadí)
        sort($migrations);
    }

    if (empty($migrations)) {
        echo "Žádné migrační soubory nebyly nalezeny v složce migrations.\n";
        exit(0);
    }

    echo "Nalezeno " . count($migrations) . " migračních souborů:\n";
    foreach ($migrations as $migration) {
        echo "  - {$migration}\n";
    }
    echo "\n";

    foreach ($migrations as $migration) {
        $file = $migrationsDir . '/' . $migration;

        if (file_exists($file)) {
            echo "Spouštím migraci: {$migration}\n";

            $sql = file_get_contents($file);
            $connection->executeStatement($sql);

            // Přejmenování souboru na .success po úspěšném spuštění
            $successFile = $file . '.success';
            if (rename($file, $successFile)) {
                echo "✓ Migrace {$migration} dokončena a označena jako úspěšná\n";
            } else {
                echo "✓ Migrace {$migration} dokončena, ale nepodařilo se označit jako úspěšnou\n";
            }
        } else {
            echo "✗ Soubor {$migration} nebyl nalezen\n";
        }
    }

    echo "\nVšechny migrace byly úspěšně spuštěny!\n";

} catch (Exception $e) {
    echo "Chyba při spouštění migrací: " . $e->getMessage() . "\n";
    exit(1);
}
