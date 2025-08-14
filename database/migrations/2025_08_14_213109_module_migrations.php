<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

class ModuleMigrations extends AbstractMigration
{
    protected function migrate(): void
    {
        $table = $this->createTable('module_migrations');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('module_id', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('file', Types::STRING, ['length' => 500, 'notnull' => true]);
        $table->addColumn('type', Types::STRING, ['length' => 50, 'notnull' => true]);
        $table->addColumn('table_name', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('sql_content', Types::TEXT, ['notnull' => false]);
        $table->addColumn('status', Types::STRING, ['length' => 20, 'default' => 'pending']);
        $table->addColumn('ran_at', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('error_message', Types::TEXT, ['notnull' => false]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['module_id'], 'idx_module_migrations_module_id');
        $table->addIndex(['status'], 'idx_module_migrations_status');
        $table->addIndex(['file'], 'idx_module_migrations_file');

        // Foreign key na modules tabulku
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], [
            'onDelete' => 'CASCADE',
            'onUpdate' => 'CASCADE'
        ], 'fk_module_migrations_module_id');
    }

    protected function rollback(): void
    {
        $this->dropTable('module_migrations');
    }
}
