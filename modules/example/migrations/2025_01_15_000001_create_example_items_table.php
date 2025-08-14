<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class CreateExampleItemsTable extends AbstractMigration
{
    protected function migrate(): void
    {
        $this->setDescription('Vytvoření tabulky example_items pro modul Example');

        $table = $this->createTable('example_items');
        
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', Types::TEXT, ['notnull' => false]);
        $table->addColumn('status', Types::STRING, ['length' => 50, 'notnull' => true, 'default' => 'active']);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => true]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true]);
        
        $table->setPrimaryKey(['id']);
        
        $this->raw($this->schema->toSql($this->connection->getDatabasePlatform())[0]);
    }

    protected function rollback(): void
    {
        $this->raw("DROP TABLE IF EXISTS `example_items`");
    }
}
