<?php

use Core\Facades\Container;

echo "Spouštím entity seedery...\n";

try {
    $em = Container::get('doctrine.em');

    // Seznam entity seed souborů (user_seed.php musí být první)
    $entitySeeds = [
        // 'user_seed.php',
        // 'customer_seed.php',
        // 'deal_seed.php',
        // 'contact_seed.php',
        // 'invoice_seed.php',
        // 'workflow_seed.php',
        'email_seeds.php'
    ];

    foreach ($entitySeeds as $seed) {
        $file = __DIR__ . '/' . $seed;
        if (file_exists($file)) {
            echo "Spouštím entity seed: {$seed}\n";
            require_once $file;
            echo "✓ Entity seed {$seed} dokončen\n";
        } else {
            echo "✗ Entity seed soubor {$seed} nebyl nalezen\n";
        }
    }

    echo "Všechny entity seedery byly úspěšně spuštěny!\n";

} catch (Exception $e) {
    echo "Chyba při spouštění entity seedů: " . $e->getMessage() . "\n";
    exit(1);
}
