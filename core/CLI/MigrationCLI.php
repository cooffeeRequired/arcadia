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
use Doctrine\ORM\Tools\SchemaTool;
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
    private ?string $entitiesDir;

    private ConsoleUI $ui;

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
        $this->entitiesDir = APP_CONFIGURATION['entities_dir'] ?? 'app/Entities';

        $this->ui = new ConsoleUI();

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
        ConsoleUI::box('Arcadia Migrations – Help', [
            ConsoleUI::strong('Použití:') . ' ./console/arcadia-migrations <příkaz> [parametry]',
            '',
            ConsoleUI::strong('Příkazy:'),
            '  migrate [--to=version]        Spustí všechny neaplikované migrace (nebo do verze)',
            '  rollback [--to=version]       Vrátí migrace zpět (poslední nebo do verze)',
            '  status [--versions]           Zobrazí stav migrací',
            '  create <název>                Vytvoří novou migraci (soubor)',
            '  generate-from-entities        Vygeneruje migraci z entit (SchemaTool)',
            '  seed [--seeder=ClassName]     Spustí seedy (vše nebo konkrétní)',
            '  create-seed <název>           Vytvoří nový seed',
            '',
            ConsoleUI::subtle('Příklady:'),
            '  ./arcadia-migrations create create_users_table',
            '  ./arcadia-migrations generate-from-entities',
            '  ./arcadia-migrations migrate --to=2025_08_08_200234',
            '  ./arcadia-migrations rollback --to=2025_08_08_184014',
            '  ./arcadia-migrations status --versions',
            '  ./arcadia-migrations create-seed UsersSeeder',
            '  ./arcadia-migrations migrate',
            '  ./arcadia-migrations seed',
        ], 'info', 2, 1);
    }

    private function createMigration($name): void
    {
        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_$name.php";
        $filepath = $this->migrationsDir . '/' . $filename;

        file_put_contents($filepath, $this->getMigrationTemplate($name));

        ConsoleUI::box('Nová migrace', [
            ConsoleUI::ok('Soubor vytvořen'),
            "Cesta: {$filepath}",
            "Verze: {$timestamp}",
            ConsoleUI::subtle("Upravte soubor a poté spusťte: migrate"),
        ], 'success', 2);
    }


    private function createSeed($name): void
    {
        $filepath = $this->seedsDir . '/' . "$name.php";
        file_put_contents($filepath, $this->getSeedTemplate($name));

        ConsoleUI::box('Nový seed', [
            ConsoleUI::ok('Soubor vytvořen'),
            "Cesta: {$filepath}",
            ConsoleUI::subtle("Upravte soubor a poté spusťte: seed"),
        ], 'success', 2);
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
            ConsoleUI::box('Migrace', [ConsoleUI::warn('Žádné migrace k spuštění.')], 'warning', 2);
            return;
        }

        foreach ($migrationsToRun as $migrationInfo) {
            $migrationName = $migrationInfo['name'];
            $className = $migrationInfo['class'];
            $version = $migrationInfo['version'];

            $title = "{$migrationInfo['version']} — {$migrationInfo['name']}";
            ConsoleUI::box('Spouštím migraci', [$title], 'info', 2);

            try {
                $migration = new $className();
                $this->executeMigration($migration, 'up');
                $this->connection->insert($this->migrationsTable, [
                    'version' => $version,
                    'description' => $migration->getDescription(),
                    'executed_at' => date('Y-m-d H:i:s')
                ]);
                ConsoleUI::box('OK', [ConsoleUI::ok("Migrace dokončena: {$migrationName}")], 'success', 2);
            } catch (Exception $e) {
                ConsoleUI::box('Chyba migrace', [
                    ConsoleUI::err("{$migrationName}"),
                    ConsoleUI::subtle($e->getMessage()),
                ], 'error', 2);
                ConsoleUI::box('Stop', [ConsoleUI::warn('Spouštění migrací bylo zastaveno kvůli chybě.')], 'warning', 2);
                return;
            }
        }
        ConsoleUI::box('Migrace', [ConsoleUI::ok('Všechny migrace dokončeny.')], 'success', 2);
    }

    /**
     * @throws SchemaException
     * @throws \Doctrine\DBAL\Exception
     */
    private function rollback($targetVersion = null): void
    {
        $executedMigrations = $this->getExecutedMigrationsWithDetails();

        if (empty($executedMigrations)) {
            ConsoleUI::box('Rollback', [ConsoleUI::warn('Žádné migrace k vrácení zpět.')], 'warning', 2);
            return;
        }

        $migrationsToRollback = [];

        if ($targetVersion) {
            foreach (array_reverse($executedMigrations) as $migration) {
                if (strcmp($migration['version'], $targetVersion) > 0) {
                    $migrationsToRollback[] = $migration;
                } else {
                    break;
                }
            }
        } else {
            $migrationsToRollback = [end($executedMigrations)];
        }


        if (empty($migrationsToRollback)) {
            ConsoleUI::box('Rollback', [ConsoleUI::warn('Žádné migrace k vrácení zpět.')], 'warning', 2);
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
                    ConsoleUI::box('Rollback', [
                        ConsoleUI::strong($version),
                        $description ? ConsoleUI::subtle($description) : '',
                    ], 'warning', 2);

                    try {
                        $migration = new $className();
                        $this->executeMigration($migration, 'down');
                        $this->connection->delete($this->migrationsTable, ['version' => $version]);

                        ConsoleUI::box('OK', [ConsoleUI::ok("Migrace vrácena: {$version}")], 'success', 2);

                    } catch (Exception $e) {
                        ConsoleUI::box('Chyba rollbacku', [
                            ConsoleUI::err($version),
                            ConsoleUI::subtle($e->getMessage()),
                        ], 'error', 2);
                        ConsoleUI::box('Stop', [ConsoleUI::warn('Vrácení migrací bylo zastaveno kvůli chybě.')], 'warning', 2);
                        return;
                    }
                }
            }
        }
    }

    /** @noinspection D */
    private function status($showVersions = false): void
    {
        try {
            $connection = $this->connection;
            $schemaManager = $connection->createSchemaManager();
            $migrationsTable = $this->migrationsTable ?? 'migrations';
            $exists = $schemaManager->tablesExist([$migrationsTable]);

            ConsoleUI::box('Stav databáze', [
                // Hlava
            ], 'info', 2);

            ConsoleUI::section('Připojení');
            ConsoleUI::kv('Databáze:', $_ENV['DB_DATABASE'] ?? 'arcadia');
            ConsoleUI::kv('Host:', $_ENV['DB_HOST'] ?? 'localhost');
            ConsoleUI::kv('Stav:', ConsoleUI::ok('OK'));

            if (!$exists) {
                ConsoleUI::box('Migrace', [ConsoleUI::warn('Tabulka migrací neexistuje. Spusťte první migraci.')], 'warning', 2);
                return;
            }

            $executed = $connection->fetchAllAssociative("SELECT version, description, executed_at FROM {$migrationsTable} ORDER BY id");
            $files = glob($this->migrationsDir . '/*.php');

            ConsoleUI::section('Migrace – Souhrn');
            ConsoleUI::kv('Spuštěné:', (string)count($executed));
            ConsoleUI::kv('Souborů:', (string)count($files));

            if ($showVersions && !empty($executed)) {
                ConsoleUI::section('Historie verzí');
                $rows = [['Version', 'Description']];
                foreach ($executed as $m) {
                    $rows[] = [$m['version'], $m['description'] ?? ''];
                }
                ConsoleUI::table($rows, array_shift($rows));
            }

            if (!empty($executed)) {
                ConsoleUI::section('Poslední migrace');
                $last = array_slice($executed, -5);
                $rows = [['Version', 'Executed at', 'Description']];
                foreach ($last as $m) {
                    $rows[] = [$m['version'], $m['executed_at'] ?? '', $m['description'] ?? ''];
                }
                ConsoleUI::table($rows, array_shift($rows));
            }

            // Pending
            $pending = [];
            foreach ($files as $file) {
                $migrationName = basename($file, '.php');
                $version = $this->extractVersionFromFilename($migrationName);
                $isExecuted = false;
                foreach ($executed as $e) {
                    if ($e['version'] === $version) { $isExecuted = true; break; }
                }
                if (!$isExecuted) {
                    require_once $file;
                    $className = $this->getClassNameFromFile($file);
                    if (class_exists($className)) {
                        $migration = new $className();
                        $d = method_exists($migration, 'getDescription') ? $migration->getDescription() : '';
                        $pending[] = [$version, $d];
                    } else {
                        $pending[] = [$version, ''];
                    }
                }
            }

            if (!empty($pending)) {
                ConsoleUI::section('Čekající migrace');
                $rows = [['Version', 'Description'], ...$pending];
                ConsoleUI::table($rows, array_shift($rows));
            } else {
                ConsoleUI::box('Migrace', [ConsoleUI::ok('Všechny migrace jsou spuštěny')], 'success', 2);
            }
        } catch (\Exception $e) {
            ConsoleUI::box('Chyba připojení', [
                ConsoleUI::err($e->getMessage())
            ], 'error', 2);
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
            ConsoleUI::box('Seedy', [ConsoleUI::warn('Adresář seeds neexistuje.')], 'warning', 2);
            return;
        }

        if ($targetSeeder) {
            $seedFile = $seedsDir . '/' . $targetSeeder . '.php';
            if (!file_exists($seedFile)) {
                ConsoleUI::box('Seedy', [ConsoleUI::err("Seeder '{$targetSeeder}' neexistuje: {$seedFile}")], 'error', 2);
                return;
            }

            ConsoleUI::box('Seeder', ["{$targetSeeder}"], 'info', 2);
            require_once $seedFile;

            if (class_exists($targetSeeder)) {
                $seed = new $targetSeeder();
                $builder = $this->connection->createQueryBuilder();
                $seed->run($this->connection, $builder);
                ConsoleUI::box('OK', [ConsoleUI::ok("Seeder '{$targetSeeder}' dokončen.")], 'success', 2);
            } else {
                ConsoleUI::box('Seedy', [ConsoleUI::err("Třída '{$targetSeeder}' neexistuje.")], 'error', 2);
            }
            return;
        }

        $filteredFiles = glob($seedsDir . '/*.php');

        ConsoleUI::section('Pořadí seedů');
        ConsoleUI::items(array_map(fn($p) => basename($p), $filteredFiles));

        foreach ($filteredFiles as $file) {
            $className = basename($file, '.php');
            ConsoleUI::box('Seeder', [$className], 'info', 2);
            require_once $file;
            if (class_exists($className)) {
                $seed = new $className();
                $builder = $this->connection->createQueryBuilder();
                $seed->run($this->connection, $builder);
                echo ConsoleUI::ok("Hotovo: {$className}") . "\n\n";
            }
        }
        ConsoleUI::box('Seedy', [ConsoleUI::ok('Všechny seedy dokončeny.')], 'success', 2);
    }

    private function generateFromEntities(): void
    {
        ConsoleUI::box('Generování z entit', [ConsoleUI::info('Start…')], 'info', 2);

        $entities = $this->scanEntities();
        if (empty($entities)) {
            ConsoleUI::box('Generování z entit', [ConsoleUI::warn('Žádné entity nebyly nalezeny.')], 'warning', 2);
            return;
        }

        $timestamp = date('Y_m_d_His');
        $filename = "{$timestamp}_create_tables_from_entities.php";
        $filepath = $this->migrationsDir . '/' . $filename;

        try {
            $migrationContent = $this->generateMigrationFromEntities($entities);
            file_put_contents($filepath, $migrationContent);

            ConsoleUI::section('Výsledek');
            ConsoleUI::kv('Soubor:', $filepath);
            ConsoleUI::kv('Verze:', $timestamp);
            ConsoleUI::kv('Počet entit:', (string)count($entities));
            ConsoleUI::box('Pokračování', ['Spusťte příkaz: ./console/arcadia-migrations migrate'], 'info', 2);
        } catch (\Throwable $e) {
            ConsoleUI::box('Chyba generování', [
                ConsoleUI::err($e->getMessage())
            ], 'error', 2);
        }
    }

    /**
     * Naskenuje všechny entity v adresáři
     */
    private function scanEntities(): array
    {
        $entities = [];
        $entityFiles = glob($this->entitiesDir . '/*.php');

        foreach ($entityFiles as $file) {
            $className = basename($file, '.php');
            $fullClassName = "App\\Entities\\$className";

            if (class_exists($fullClassName)) {
                $entities[] = [
                    'file' => $file,
                    'className' => $className,
                    'fullClassName' => $fullClassName
                ];
            }
        }

        return $entities;
    }

    /**
     * Vygeneruje obsah migrace z entit
     */
    private function generateMigrationFromEntities(array $entities): string
    {
        $className = 'CreateTablesFromEntities';
        $migrationCode = "<?php /** @noinspection PhpUnhandledExceptionInspection */\n\n";
        $migrationCode .= "use Core\\CLI\\AbstractMigration;\n";
        $migrationCode .= "use Doctrine\\DBAL\\Query\\QueryBuilder;\n";
        $migrationCode .= "use Doctrine\\DBAL\\Schema\\Schema;\n";
        $migrationCode .= "use Doctrine\\DBAL\\Schema\\Table;\n";
        $migrationCode .= "use Doctrine\\DBAL\\Types\\Types;\n\n";
        $migrationCode .= "class $className extends AbstractMigration\n";
        $migrationCode .= "{\n";
        $migrationCode .= "    protected function migrate(): void\n";
        $migrationCode .= "    {\n";
        $migrationCode .= "        \$this->setDescription('Aktualizace schématu databáze podle entit: " . implode(', ', array_column($entities, 'className')) . "');\n\n";

        // Generování CREATE TABLE příkazů pomocí SchemaTool
        $migrationCode .= $this->generateTableCreationSQL($entities);

        $migrationCode .= "    }\n\n";
        $migrationCode .= "    protected function rollback(): void\n";
        $migrationCode .= "    {\n";

        // Generování DROP TABLE příkazů pouze pro tabulky, které byly vytvořeny
        $migrationCode .= $this->generateRollbackSQL($entities);

        $migrationCode .= "    }\n";
        $migrationCode .= "}\n";

        return $migrationCode;
    }

                /**
     * Vygeneruje SQL pro vytvoření/aktualizaci tabulek z entit pomocí SchemaTool
     */
    private function generateTableCreationSQL(array $entities): string
    {
        $code = "";

        try {
            // Získáme EntityManager
            $em = Container::get('doctrine.em');
            $metadata = [];
            foreach ($entities as $entity) {
                $metadata[] = $em->getClassMetadata($entity['fullClassName']);
            }
            $schemaTool = new SchemaTool($em);
            $sqls = $schemaTool->getUpdateSchemaSql($metadata, true);
            $createTableSqls = [];
            $alterTableSqls = [];

            foreach ($sqls as $sql) {
                if (stripos($sql, 'CREATE TABLE') !== false) {
                    $createTableSqls[] = $sql;
                } elseif (stripos($sql, 'ALTER TABLE') !== false) {
                    $alterTableSqls[] = $sql;
                } else {
                    $createTableSqls[] = $sql; // Ostatní příkazy (DROP, atd.)
                }
            }

            // Spojíme příkazy v správném pořadí
            $sqls = array_merge($createTableSqls, $alterTableSqls);

            if (empty($sqls)) {
                $code .= "        // Žádné změny v databázi nejsou potřeba\n";
                $code .= "        // Všechny tabulky již existují a mají správnou strukturu\n\n";
            } else {
                // Přidáme každý SQL příkaz do migrace
                foreach ($sqls as $sql) {
                    $code .= "        \$this->raw(\"$sql\");\n";
                }
                $code .= "\n";
            }

        } catch (Exception $e) {
            ConsoleUI::box('Chyba při generování SQL', [
                ConsoleUI::err($e->getMessage())
            ], 'error', 2);
            // Fallback na jednoduché CREATE TABLE
            $code .= "        // Fallback: Vytvoření základní tabulky\n";
            $code .= "        \$this->raw(\"CREATE TABLE IF NOT EXISTS `users` (\n";
            $code .= "            `id` INT AUTO_INCREMENT PRIMARY KEY,\n";
            $code .= "            `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP\n";
            $code .= "        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci\");\n\n";
        }

        return $code;
    }

    /**
     * Vygeneruje SQL pro rollback - pouze pro tabulky, které byly vytvořeny
     */
    private function generateRollbackSQL(array $entities): string
    {
        $code = "";

        try {
            // Získáme EntityManager
            $em = Container::get('doctrine.em');

            // Získáme metadata pro všechny entity
            $metadata = [];
            foreach ($entities as $entity) {
                $metadata[] = $em->getClassMetadata($entity['fullClassName']);
            }

            // Použijeme SchemaTool pro generování SQL
            $schemaTool = new SchemaTool($em);

            // Získáme SQL pro aktualizaci schématu
            $sqls = $schemaTool->getUpdateSchemaSql($metadata, true);

            if (empty($sqls)) {
                $code .= "        // Žádné tabulky nebyly vytvořeny, rollback není potřeba\n";
            } else {
                // Generujeme DROP TABLE příkazy pro všechny entity v opačném pořadí
                $reversedEntities = array_reverse($entities);
                foreach ($reversedEntities as $entity) {
                    try {
                        $tableName = $em->getClassMetadata($entity['fullClassName'])->getTableName();
                        $code .= "        \$this->raw(\"DROP TABLE IF EXISTS `$tableName`\");\n";
                    } catch (Exception $e) {
                        // Fallback na snake_case název
                        $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $entity['className']));
                        $code .= "        \$this->raw(\"DROP TABLE IF EXISTS `$tableName`\");\n";
                    }
                }
            }

        } catch (Exception $e) {
            ConsoleUI::box('Chyba při generování rollback SQL', [
                ConsoleUI::err($e->getMessage()),
                ConsoleUI::subtle('Použiji fallback DROP pro všechny odvozené názvy tabulek.')
            ], 'error', 2);

            $code .= "        \$this->raw(\"SET FOREIGN_KEY_CHECKS = 0;\");\n";
            $reversedEntities = array_reverse($entities);
            foreach ($reversedEntities as $entity) {
                $tableName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $entity['className']));
                $code .= "        \$this->raw(\"DROP TABLE IF EXISTS `{$tableName}`;\");\n";
            }
            $code .= "        \$this->raw(\"SET FOREIGN_KEY_CHECKS = 1;\");\n";
        }

        return $code;
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


    /**
     * @throws SchemaException
     * @throws \Doctrine\DBAL\Exception
     */
    private function executeMigration(AbstractMigration $migration, $method): void
    {
        $connection = $this->connection;

        try {
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

        } catch (Exception $e) {
            ConsoleUI::box('Chyba při migraci', [
                ConsoleUI::err($e->getMessage()),
                ConsoleUI::subtle('Změny budou vráceny zpět.'),
            ], 'error', 2);
            throw $e;
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
            case 'generate-from-entities':
                $this->generateFromEntities();
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
            if (str_starts_with($arg, '--')) {
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
