<?php

namespace Core\CLI;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

abstract class AbstractMigration
{
    public array $queries = [];
    public ?string $version = null;
    public ?string $description = null;

    protected Connection $connection;
    protected Schema $schema;
    protected QueryBuilder $queryBuilder;

    public function __construct()
    {
        // Version bude nastavena automaticky z názvu souboru
        $this->version = $this->extractVersionFromClassName();
    }

    /**
     * Extrahuje verzi z názvu třídy (timestamp)
     */
    private function extractVersionFromClassName(): ?string
    {
        $className = static::class;
        if (preg_match('/Version(\d{4}_\d{2}_\d{2}_\d{6})/', $className, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Hlavní metoda pro spuštění migrace nahoru
     */
    public function up(Schema $schema, QueryBuilder $builder): void
    {
        $this->schema = $schema;
        $this->queryBuilder = $builder;
        $this->migrate();
    }

    /**
     * Hlavní metoda pro spuštění migrace dolů
     */
    public function down(Schema $schema, QueryBuilder $builder): void
    {
        $this->schema = $schema;
        $this->queryBuilder = $builder;
        $this->rollback();
    }

    /**
     * Abstraktní metoda pro implementaci migrace
     */
    abstract protected function migrate(): void;

    /**
     * Abstraktní metoda pro implementaci rollbacku
     */
    abstract protected function rollback(): void;

    /**
     * Vytvoří novou tabulku
     */
    protected function createTable(string $tableName): Table
    {
        return $this->schema->createTable($tableName);
    }

    /**
     * Získá existující tabulku
     */
    protected function getTable(string $tableName): Table
    {
        return $this->schema->getTable($tableName);
    }

    /**
     * Kontroluje, zda tabulka existuje
     */
    protected function hasTable(string $tableName): bool
    {
        return $this->schema->hasTable($tableName);
    }

    /**
     * Smaže tabulku
     */
    protected function dropTable(string $tableName): void
    {
        $this->schema->dropTable($tableName);
    }

    /**
     * Přidá sloupec do tabulky
     */
    protected function addColumn(string $tableName, string $columnName, string $type, array $options = []): void
    {
        $table = $this->getTable($tableName);
        $table->addColumn($columnName, $type, $options);
    }

    /**
     * Smaže sloupec z tabulky
     */
    protected function dropColumn(string $tableName, string $columnName): void
    {
        $table = $this->getTable($tableName);
        $table->dropColumn($columnName);
    }

    /**
     * Přidá index do tabulky
     */
    protected function addIndex(string $tableName, array $columns, ?string $indexName = null): void
    {
        $table = $this->getTable($tableName);
        $table->addIndex($columns, $indexName);
    }

    /**
     * Přidá unikátní index do tabulky
     */
    protected function addUniqueIndex(string $tableName, array $columns, ?string $indexName = null): void
    {
        $table = $this->getTable($tableName);
        $table->addUniqueIndex($columns, $indexName);
    }

    /**
     * Smaže index z tabulky
     */
    protected function dropIndex(string $tableName, string $indexName): void
    {
        $table = $this->getTable($tableName);
        $table->dropIndex($indexName);
    }

    /**
     * Přidá foreign key constraint
     */
    protected function addForeignKeyConstraint(
        string $tableName,
        array $localColumns,
        string $foreignTableName,
        array $foreignColumns,
        array $options = []
    ): void {
        $table = $this->getTable($tableName);
        $table->addForeignKeyConstraint($foreignTableName, $localColumns, $foreignColumns, $options);
    }

    /**
     * Smaže foreign key constraint
     */
    protected function dropForeignKeyConstraint(string $tableName, string $constraintName): void
    {
        $table = $this->getTable($tableName);
        $table->removeForeignKey($constraintName);
    }

    /**
     * Spustí raw SQL dotaz
     */
    protected function raw(string|array $sql): void
    {
        if (is_array($sql)) {
            $this->queries = array_merge($this->queries, $sql);
        } else {
            $this->queries[] = $sql;
        }
    }

    /**
     * Vymaže raw SQL dotazy
     */
    public function clearQueries(): void
    {
        $this->queries = [];
    }

    /**
     * Získá raw SQL dotazy
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * Nastaví popis migrace
     */
    protected function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Získá popis migrace
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Nastaví verzi migrace
     */
    protected function setVersion(string $version): void
    {
        $this->version = $version;
    }

    /**
     * Získá verzi migrace
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }
}
