<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

class ModuleControllers extends AbstractMigration
{
    protected function migrate(): void
    {
        $table = $this->createTable('module_controllers');
        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('module_id', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('namespace', Types::STRING, ['length' => 500, 'notnull' => true]);
        $table->addColumn('extends', Types::STRING, ['length' => 500, 'notnull' => true]);
        $table->addColumn('methods', Types::JSON, ['notnull' => false]);
        $table->addColumn('file_path', Types::STRING, ['length' => 500, 'notnull' => true]);
        $table->addColumn('enabled', Types::BOOLEAN, ['default' => true]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['default' => 'CURRENT_TIMESTAMP']);
        
        $table->setPrimaryKey(['id']);
        $table->addIndex(['module_id'], 'idx_module_controllers_module_id');
        $table->addIndex(['name'], 'idx_module_controllers_name');
        $table->addIndex(['enabled'], 'idx_module_controllers_enabled');
        
        // Foreign key na modules tabulku
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], [
            'onDelete' => 'CASCADE',
            'onUpdate' => 'CASCADE'
        ], 'fk_module_controllers_module_id');
    }

    protected function rollback(): void
    {
        $this->dropTable('module_controllers');
    }
}
