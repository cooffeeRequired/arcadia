<?php

class InstallExample
{
    private $connection;
    private $config;

    public function __construct()
    {
        $this->config = require APP_ROOT . '/config/config.php';
        $this->connection = \Core\Facades\Container::get('doctrine.connection');
    }

    /**
     * Instaluje modul
     */
    public function install(): void
    {
        echo "Instalace modulu Example...\n";

        // Vytvoření tabulky pro příklad modulu
        $this->createExampleTable();

        // Vložení výchozích dat
        $this->insertDefaultData();

        // Vytvoření oprávnění
        $this->createPermissions();

        echo "Modul Example byl úspěšně nainstalován.\n";
    }

    /**
     * Vytvoří tabulku pro příklad modulu
     */
    private function createExampleTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS example_items (
            id INT AUTO_INCREMENT NOT NULL,
            name VARCHAR(255) NOT NULL,
            description TEXT,
            status VARCHAR(50) DEFAULT 'active',
            created_at DATETIME NOT NULL,
            updated_at DATETIME NOT NULL,
            PRIMARY KEY (id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB";

        $this->connection->executeStatement($sql);
        echo "Tabulka example_items byla vytvořena.\n";
    }

    /**
     * Vloží výchozí data
     */
    private function insertDefaultData(): void
    {
        $sql = "INSERT INTO example_items (name, description, status, created_at, updated_at) VALUES 
                ('První příklad', 'Toto je první příklad položky', 'active', NOW(), NOW()),
                ('Druhý příklad', 'Toto je druhý příklad položky', 'active', NOW(), NOW())";

        $this->connection->executeStatement($sql);
        echo "Výchozí data byla vložena.\n";
    }

    /**
     * Vytvoří oprávnění pro modul
     */
    private function createPermissions(): void
    {
        // Zde by byla logika pro vytvoření oprávnění v systému
        // Pro tento příklad jen vypíšeme informaci
        echo "Oprávnění pro modul Example byla vytvořena.\n";
    }
}
