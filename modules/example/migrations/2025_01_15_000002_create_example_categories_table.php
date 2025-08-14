<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class CreateExampleCategoriesTable extends AbstractMigration
{
    protected function migrate(): void
    {
        $this->setDescription('Vytvoření tabulky pro kategorie příkladů');

        $table = $this->createTable('example_categories');

        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', Types::TEXT, ['notnull' => false]);
        $table->addColumn('parent_id', Types::INTEGER, ['notnull' => false]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => true]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['parent_id'], 'idx_example_categories_parent_id');
        $table->addIndex(['name'], 'idx_example_categories_name');
        
        // Foreign key constraint
        $table->addForeignKeyConstraint('example_categories', ['parent_id'], ['id'], [
            'onDelete' => 'SET NULL'
        ], 'fk_example_categories_parent');
    }

    protected function rollback(): void
    {
        $this->raw("DROP TABLE IF EXISTS `example_categories`");
    }
}
