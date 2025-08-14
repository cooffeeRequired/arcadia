<?php

class UninstallExample
{
    private $connection;
    private $config;

    public function __construct()
    {
        $this->config = require APP_ROOT . '/config/config.php';
        $this->connection = \Core\Facades\Container::get('doctrine.connection');
    }

    /**
     * Odinstaluje modul
     */
    public function uninstall(): void
    {
        echo "Odinstalace modulu Example...\n";

        // Smazání tabulky
        $this->dropExampleTable();

        // Smazání oprávnění
        $this->removePermissions();

        echo "Modul Example byl úspěšně odinstalován.\n";
    }

    /**
     * Smaže tabulku pro příklad modulu
     */
    private function dropExampleTable(): void
    {
        $sql = "DROP TABLE IF EXISTS example_items";
        $this->connection->executeStatement($sql);
        echo "Tabulka example_items byla smazána.\n";
    }

    /**
     * Smaže oprávnění pro modul
     */
    private function removePermissions(): void
    {
        // Zde by byla logika pro smazání oprávnění ze systému
        // Pro tento příklad jen vypíšeme informaci
        echo "Oprávnění pro modul Example byla smazána.\n";
    }
}
