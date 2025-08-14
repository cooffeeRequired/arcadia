<?php

namespace Core\CLI;

use Core\Facades\Container;
use Core\CLI\CustomSQLLogger;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaException;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\Provider\DBALSchemaDiffProvider;
use Doctrine\Migrations\Version\Version;
use Exception;

class MigrationCLI
{
    private Connection $connection {
        get {
            return $this->connection;
        }
    }
    private ?string $migrationsDir;
    private ?string $seedsDir;
    private ?string $migrationsTable = 'migrations';

    /**
     * @throws SchemaException
     * @noinspection PhpDeprecationInspection
     */
    public function __construct()
    {
        if (isset(APP_CONFIGURATION['migrations_table'])) {
            $this->migrationsTable = APP_CONFIGURATION['migrations_table'];
        }
        $this->migrationsDir = APP_CONFIGURATION['migrations_dir'];
        $this->seedsDir = APP_CONFIGURATION['seeds_dir'];

        if (! is_dir($this->migrationsDir)) mkdir($this->migrationsDir, 0755, true);
        if (! is_dir($this->seedsDir)) mkdir($this->seedsDir, 0755, true);

        $this->connection = Container::get('doctrine.connection');
        $this->connection->getConfiguration()->setSQLLogger(new CustomSQLLogger());
        $this->ensureMigrationsTable();
    }

    /**
     * @throws SchemaException
     * @throws Exception
     */
    private function ensureMigrationsTable(): void
    {
        $schemaManager = $this->connection->createSchemaManager();

        if (!$schemaManager->tablesExist([$this->migrationsTable])) {
            $schema = new Schema();
            $table = $schema->createTable($this->migrationsTable);
            $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
            $table->addColumn('version', Types::STRING, ['length' => 255]);
            $table->addColumn('executed_at', Types::DATETIME_MUTABLE);
            $table->addColumn('description', Types::STRING, ['length' => 500, 'notnull' => false]);
            $table->setPrimaryKey(['id']);
            $table->addUniqueIndex(['version']);

            $queries = $schema->toSql($this->connection->getDatabasePlatform());
            foreach ($queries as $query) {
                $this->connection->executeStatement($query);
            }
        } else {
            // Kontrola a přidání chybějících sloupců
            $columns = $schemaManager->listTableColumns($this->migrationsTable);
            if (!array_key_exists('description', $columns)) {
                $this->connection->executeStatement("ALTER TABLE {$this->migrationsTable} ADD COLUMN description VARCHAR(500) NULL");
            }
        }
    }

    private function showHelp(): void
    {
        echo "Použití: ./console/arcadia-migrations <příkaz> [parametry]\n\n";
        echo "Dostupné příkazy:\n";
        echo "  migrate [--to=version]        - Spustí všechny neaplikované migrace\n";
        echo "  rollback [--to=version]       - Vrátí migrace zpět\n";
        echo "  status [--versions]           - Zobrazí stav migrací\n";
        echo "  create <název>                - Vytvoří novou migraci\n";
        echo "  seed                          - Spustí všechny seedy\n";
        echo "  create-seed <název>           - Vytvoří nový seed\n\n";
        echo "Příklady:\n";
        echo "  ./console/arcadia-migrations create create_users_table\n";
        echo "  ./console/arcadia-migrations migrate --to=2025_08_08_200234\n";
        echo "  ./console/arcadia-migrations rollback --to=2025_08_08_184014\n";
        echo "  ./console/arcadia-migrations status --versions\n";
        echo "  ./console/arcadia-migrations create-seed UsersSeeder\n";
        echo "  ./console/arcadia-migrations migrate\n";
        echo "  ./console/arcadia-migrations seed\n";
    }

    private function createMigration($name): void
    {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_$name.php";
        $filepath = $this->migrationsDir . '/' . $filename;

        $template = $this->getMigrationTemplate($name);
        file_put_contents($filepath, $template);

        echo "Migrace vytvořena: $filepath\n";
        echo "Verze: $timestamp\n";
        echo "Upravte soubor podle vašich potřeb a pak spusťte 'migrate'\n";
    }

    private function createSeed($name): void
    {
        $seedsDir = $this->seedsDir;
        $filename = "$name.php";
        $filepath = $seedsDir . '/' . $filename;

        $template = $this->getSeedTemplate($name);
        file_put_contents($filepath, $template);

        echo "Seed vytvořen: $filepath\n";
        echo "Upravte soubor podle vašich potřeb a pak spusťte 'seed'\n";
    }

    private function getSeedTemplate($name): array|false|string
    {
        $className = $this->camelCase($name);
        $load = file_get_contents(__DIR__ . '/stubs/seed.tpl');
        return str_replace("{className}", $className, $load);
    }

    private function getMigrationTemplate($name): string
    {
        $className = $this->camelCase($name);
        $load = file_get_contents(__DIR__ . '/stubs/migrations.tpl');
        return str_replace("{className}", $className, $load);
    }

    /**
     * @throws SchemaException
     * @throws \Doctrine\DBAL\Exception
     */
    private function migrate($targetVersion = null): void
    {
        $files = glob($this->migrationsDir . '/*.php');
        sort($files);

        $executedMigrations = $this->getExecutedMigrations();
        $migrationsToRun = [];

        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            $version = $this->extractVersionFromFilename($migrationName);

            if (in_array($version, $executedMigrations)) {
                continue;
            }

            // Pokud je nastavena cílová verze, kontrolujeme
            if ($targetVersion && $version && strcmp($version, $targetVersion) > 0) {
                continue;
            }

            require_once $file;
            $className = $this->getClassNameFromFile($file);

            if (class_exists($className)) {
                $migrationsToRun[] = [
                    'file' => $file,
                    'name' => $migrationName,
                    'class' => $className,
                    'version' => $version
                ];
            }
        }

        if (empty($migrationsToRun)) {
            echo $this->colorize("Žádné migrace k spuštění.", 'yellow') . "\n";
            return;
        }

        foreach ($migrationsToRun as $migrationInfo) {
            $migrationName = $migrationInfo['name'];
            $className = $migrationInfo['class'];
            $version = $migrationInfo['version'];

            echo $this->colorize("Spuštěna migrace: $migrationName", 'green') . "\n";
            echo $this->colorize("Verze: $version", 'cyan') . "\n";
            echo $this->colorize("|", 'blue') . "\n";

            $migration = new $className();
            $this->executeMigration($migration, 'up');

            $this->connection->insert($this->migrationsTable, [
                'version' => $version,
                'description' => $migration->getDescription(),
                'executed_at' => date('Y-m-d H:i:s')
            ]);

            echo $this->colorize("|", 'blue') . "\n";
            echo $this->colorize("Migrace dokončena: $migrationName", 'green') . "\n\n";
        }

        echo $this->colorize("Všechny migrace dokončeny.", 'bold') . "\n";
    }

    /**
     * @throws SchemaException
     * @throws \Doctrine\DBAL\Exception
     */
    private function rollback($targetVersion = null): void
    {
        $executedMigrations = $this->getExecutedMigrationsWithDetails();

        if (empty($executedMigrations)) {
            echo "Žádné migrace k vrácení zpět.\n";
            return;
        }

        $migrationsToRollback = [];

        if ($targetVersion) {
            // Vrácení na konkrétní verzi
            foreach (array_reverse($executedMigrations) as $migration) {
                if (strcmp($migration['version'], $targetVersion) > 0) {
                    $migrationsToRollback[] = $migration;
                } else {
                    break;
                }
            }
        } else {
            // Vrácení poslední migrace
            $migrationsToRollback = [end($executedMigrations)];
        }

        if (empty($migrationsToRollback)) {
            echo "Žádné migrace k vrácení zpět.\n";
            return;
        }

        foreach ($migrationsToRollback as $migrationInfo) {
            $version = $migrationInfo['version'];
            $description = $migrationInfo['description'];

            $file = $this->findMigrationFileByVersion($version);

            if ($file && file_exists($file)) {
                require_once $file;
                $className = $this->getClassNameFromFile($file);

                if (class_exists($className)) {
                    echo $this->colorize("Vrácení migrace: $version", 'yellow') . "\n";
                    if ($description) {
                        echo $this->colorize("Popis: $description", 'cyan') . "\n";
                    }
                    echo $this->colorize("|", 'blue') . "\n";

                    $migration = new $className();
                    $this->executeMigration($migration, 'down');
                    $this->connection->delete($this->migrationsTable, ['version' => $version]);
                    echo $this->colorize("|", 'blue') . "\n";
                    echo $this->colorize("Migrace vrácena zpět: $version", 'yellow') . "\n\n";
                }
            }
        }
    }

    /** @noinspection D */
    private function status($showVersions = false): void
    {
        try {
            $connection = $this->connection;

            // Kontrola tabulky migrací
            $schemaManager = $connection->createSchemaManager();
            $migrationsTableExists = $schemaManager->tablesExist([$this->migrationsTable ?? 'migrations']);

            echo "=== Stav databáze ===\n";
            echo "Databáze: " . ($_ENV['DB_DATABASE'] ?? 'arcadia') . "\n";
            echo "Host: " . ($_ENV['DB_HOST'] ?? 'localhost') . "\n";
            echo "Připojení: ✓ OK\n\n";

            if ($migrationsTableExists) {
                $executedMigrations = $connection->fetchAllAssociative("SELECT version, description, executed_at FROM migrations ORDER BY id");
                $migrationFiles = glob($this->migrationsDir . '/*.php');

                echo "=== Stav migrací ===\n";
                echo "Spuštěné migrace: " . count($executedMigrations) . "\n";
                echo "Celkem souborů: " . count($migrationFiles) . "\n\n";

                if ($showVersions && !empty($executedMigrations)) {
                    echo "Historie verzí:\n";
                    foreach ($executedMigrations as $migration) {
                        $description = $migration['description'] ? " - {$migration['description']}" : "";
                        echo "  - Verze {$migration['version']}{$description}\n";
                    }
                    echo "\n";
                }

                if (!empty($executedMigrations)) {
                    echo "Poslední spuštěné migrace:\n";
                    foreach (array_slice($executedMigrations, -5) as $migration) {
                        $description = $migration['description'] ? " ({$migration['description']})" : "";
                        echo "  - {$migration['version']}{$description} ({$migration['executed_at']})\n";
                    }
                }

                $pendingMigrations = [];
                foreach ($migrationFiles as $file) {
                    $migrationName = basename($file, '.php');
                    $version = $this->extractVersionFromFilename($migrationName);

                    $executed = false;
                    foreach ($executedMigrations as $executedMigration) {
                        if ($executedMigration['version'] === $version) {
                            $executed = true;
                            break;
                        }
                    }

                    if (!$executed) {
                        require_once $file;
                        $className = $this->getClassNameFromFile($file);
                        if (class_exists($className)) {
                            $migration = new $className();
                            $description = $migration->getDescription();
                            $descriptionInfo = $description ? " - $description" : "";
                            $pendingMigrations[] = $version . $descriptionInfo;
                        } else {
                            $pendingMigrations[] = $version;
                        }
                    }
                }

                if (!empty($pendingMigrations)) {
                    echo "\nČekající migrace:\n";
                    foreach ($pendingMigrations as $migration) {
                        echo "  - $migration\n";
                    }
                } else {
                    echo "\nVšechny migrace jsou spuštěny ✓\n";
                }
            } else {
                echo "Tabulka migrací neexistuje. Spusťte první migraci.\n";
            }

            // Kontrola seedů
            $seedsDir = $this->seedsDir;
            $seedFiles = glob($seedsDir . '/*.php');
            echo "\n=== Seedy ===\n";
            echo "Dostupné seedy: " . count($seedFiles) . "\n";
            if (!empty($seedFiles)) {
                foreach ($seedFiles as $file) {
                    echo "  - " . basename($file, '.php') . "\n";
                }
            }

        } catch (Exception $e) {
            echo "Chyba připojení k databázi: " . $e->getMessage() . "\n";
        }
    }

    /**
     * @throws SchemaException
     * @throws \Doctrine\DBAL\Exception
     */
    private function seed(?string $targetSeeder = null): void
    {
        $seedsDir = $this->seedsDir;

        if (!is_dir($seedsDir)) {
            echo "Adresář seeds neexistuje.\n";
            return;
        }

        if ($targetSeeder) {
            // Spustíme pouze konkrétní seeder
            $seedFile = $seedsDir . '/' . $targetSeeder . '.php';
            if (!file_exists($seedFile)) {
                echo "❌ Seeder '$targetSeeder' neexistuje: $seedFile\n";
                return;
            }

            echo "Spouštím seeder: $targetSeeder\n";
            require_once $seedFile;

            if (class_exists($targetSeeder)) {
                $seed = new $targetSeeder();
                $builder = $this->connection->createQueryBuilder();
                $seed->run($this->connection, $builder);
                echo "✅ Seeder '$targetSeeder' dokončen.\n";
            } else {
                echo "❌ Třída '$targetSeeder' neexistuje.\n";
            }
            return;
        }

        $files = glob($seedsDir . '/*.php');
        // Seřadíme seedy podle priority (závislosti)
        $seedOrder = [
            'UserSeeder.php',
            'CustomerSeeder.php',
            'ContactSeeder.php',
            'DealSeeder.php',
            'ActivitySeeder.php',
            'EmailTemplateSeeder.php',
            'EmailSignatureSeeder.php',
            'EmailServerSeeder.php',
            'InvoiceSeeder.php'
        ];

        // Filtrujeme pouze seedy v definovaném pořadí
        $filteredFiles = [];
        foreach ($seedOrder as $seedName) {
            $seedPath = $seedsDir . '/' . $seedName;
            if (file_exists($seedPath)) {
                $filteredFiles[] = $seedPath;
            }
        }

        // Debug: vypíšeme pořadí seedů
        echo "Spouštím seedy v pořadí:\n";
        foreach ($filteredFiles as $file) {
            echo "- " . basename($file) . "\n";
        }
        echo "\n";

        foreach ($filteredFiles as $file) {
            require_once $file;
            $className = basename($file, '.php');

            if (class_exists($className)) {
                $seed = new $className();
                $builder = $this->connection->createQueryBuilder();
                $seed->run($this->connection, $builder);
            }
        }

        echo "Všechny seedy dokončeny.\n";
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getExecutedMigrations(): array
    {
        $result = $this->connection->fetchAllAssociative(
            "SELECT version FROM $this->migrationsTable"
        );

        return array_column($result, 'version');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function getExecutedMigrationsWithDetails(): array
    {
        return $this->connection->fetchAllAssociative(
            "SELECT version, description, executed_at FROM $this->migrationsTable ORDER BY id"
        );
    }

    private function getClassNameFromFile($file): string
    {
        $content = file_get_contents($file);
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }
        return basename($file, '.php');
    }

    private function extractVersionFromFilename($filename): string
    {
        if (preg_match('/^(\d{4}_\d{2}_\d{2}_\d{6})_/', $filename, $matches)) {
            return $matches[1];
        }
        return $filename;
    }

    private function findMigrationFileByVersion($version): ?string
    {
        $files = glob($this->migrationsDir . '/*.php');
        foreach ($files as $file) {
            $migrationName = basename($file, '.php');
            if ($this->extractVersionFromFilename($migrationName) === $version) {
                return $file;
            }
        }
        return null;
    }

    private function camelCase($string): array|string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    private function colorize($text, $color): string
    {
        $colors = [
            'red' => "\033[31m",
            'green' => "\033[32m",
            'yellow' => "\033[33m",
            'blue' => "\033[34m",
            'magenta' => "\033[35m",
            'cyan' => "\033[36m",
            'white' => "\033[37m",
            'bold' => "\033[1m",
            'reset' => "\033[0m"
        ];

        return $colors[$color] . $text . $colors['reset'];
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    private function executeMigration(AbstractMigration $migration, $method): void
    {
        $connection = $this->connection;
        $scm = $connection->createSchemaManager();
        $schemeProvider = new DBALSchemaDiffProvider($scm, $connection->getDatabasePlatform());
        $fromScheme = $schemeProvider->createFromSchema();
        $toScheme = $schemeProvider->createToSchema($fromScheme);
        $queryBuilder = $connection->createQueryBuilder();
        $migration->clearQueries();
        $migration->$method($toScheme, $queryBuilder);

        // Pak spustíme raw SQL dotazy
        $schemeDiffSQL = $schemeProvider->getSqlDiffToMigrate($fromScheme, $toScheme);
        $rawQueries = $migration->getQueries();
        $rawQueries = array_merge($schemeDiffSQL, $rawQueries);


        if (!empty($rawQueries)) {
            foreach ($rawQueries as $query) {
                $connection->executeStatement($query);
            }
        }
    }

    /**
     * @throws SchemaException
     * @throws \Doctrine\DBAL\Exception
     */
    public function run(array $args): void
    {
        if (empty($args)) {
            $this->showHelp();
            return;
        }

        $command = $args[0];
        $options = $this->parseOptions(array_slice($args, 1));

        switch ($command) {
            case 'migrate':
                CustomSQLLogger::enable();
                $targetVersion = $options['to'] ?? null;
                $this->migrate($targetVersion);
                CustomSQLLogger::disable();
                break;
            case 'rollback':
                CustomSQLLogger::enable();
                $targetVersion = $options['to'] ?? null;
                $this->rollback($targetVersion);
                CustomSQLLogger::disable();
                break;
            case 'status':
                $showVersions = isset($options['versions']);
                $this->status($showVersions);
                break;
            case 'create':
                if (empty($args[1])) {
                    echo "Chyba: Musíte zadat název migrace\n";
                    return;
                }
                $this->createMigration($args[1]);
                break;
            case 'seed':
                $targetSeeder = $options['seeder'] ?? null;
                $this->seed($targetSeeder);
                break;
            case 'create-seed':
                if (empty($args[1])) {
                    echo "Chyba: Musíte zadat název seedu\n";
                    return;
                }
                $this->createSeed($args[1]);
                break;
            default:
                $this->showHelp();
        }
    }

    private function parseOptions(array $args): array
    {
        $options = [];
        foreach ($args as $arg) {
            if (strpos($arg, '--') === 0) {
                $parts = explode('=', substr($arg, 2), 2);
                if (count($parts) === 2) {
                    $options[$parts[0]] = $parts[1];
                } else {
                    $options[$parts[0]] = true;
                }
            }
        }
        return $options;
    }
}
