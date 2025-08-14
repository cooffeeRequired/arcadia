<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class CreateExamplesTable extends AbstractMigration
{
    protected function migrate(): void
    {
        $this->setDescription('Vytvoření tabulky pro příklady');

        $table = $this->createTable('examples');

        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', Types::TEXT, ['notnull' => false]);
        $table->addColumn('status', Types::STRING, ['length' => 20, 'notnull' => true, 'default' => 'active']);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => true]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['status'], 'idx_examples_status');
        $table->addIndex(['created_at'], 'idx_examples_created_at');
    }

    protected function rollback(): void
    {
        $this->raw("DROP TABLE IF EXISTS `examples`");
    }
}
