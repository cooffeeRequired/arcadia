<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

class CreateModuleTables extends AbstractMigration
{

    public ?string $description = 'Aktualizace schématu databáze pro Modules';

    protected function migrate(): void
    {
        // Tabulka modulů
        $table = $this->createTable('modules');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 100]);
        $table->addColumn('display_name', 'string', ['length' => 255]);
        $table->addColumn('description', 'text', ['notnull' => false]);
        $table->addColumn('version', 'string', ['length' => 20]);
        $table->addColumn('author', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('icon', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('color', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('sort_order', 'integer', ['notnull' => false]);
        $table->addColumn('dependencies', 'json', ['notnull' => false]);
        $table->addColumn('settings', 'json', ['notnull' => false]);
        $table->addColumn('permissions', 'json', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');
        $table->addColumn('install_date', 'datetime', ['notnull' => false]);
        $table->addColumn('is_enabled', 'boolean', ['default' => false]);
        $table->addColumn('is_installed', 'boolean', ['default' => false]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name']);

        // Tabulka controllerů modulů
        $table = $this->createTable('module_controllers');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('module_id', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('namespace', 'string', ['length' => 500]);
        $table->addColumn('extends', 'string', ['length' => 500]);
        $table->addColumn('methods', 'json', ['notnull' => false]);
        $table->addColumn('file_path', 'string', ['length' => 500]);
        $table->addColumn('enabled', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], ['onDelete' => 'CASCADE']);

        // Tabulka entit modulů
        $table = $this->createTable('module_entities');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('module_id', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('table_name', 'string', ['length' => 255]);
        $table->addColumn('namespace', 'string', ['length' => 500]);
        $table->addColumn('extends', 'string', ['length' => 500]);
        $table->addColumn('properties', 'json', ['notnull' => false]);
        $table->addColumn('file_path', 'string', ['length' => 500]);
        $table->addColumn('table_exists', 'boolean', ['default' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], ['onDelete' => 'CASCADE']);

        // Tabulka migrací modulů
        $table = $this->createTable('module_migrations');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('module_id', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('file', 'string', ['length' => 500]);
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->addColumn('table_name', 'string', ['length' => 255, 'notnull' => false]);
        $table->addColumn('sql_content', 'text', ['notnull' => false]);
        $table->addColumn('status', 'string', ['length' => 20, 'default' => 'pending']);
        $table->addColumn('ran_at', 'datetime', ['notnull' => false]);
        $table->addColumn('error_message', 'text', ['notnull' => false]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], ['onDelete' => 'CASCADE']);

        // Tabulka views modulů
        $table = $this->createTable('module_views');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('module_id', 'integer');
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('file_path', 'string', ['length' => 500]);
        $table->addColumn('type', 'string', ['length' => 50]);
        $table->addColumn('enabled', 'boolean', ['default' => true]);
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], ['onDelete' => 'CASCADE']);

        // Tabulka překladů modulů
        $table = $this->createTable('module_translations');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('module_id', 'integer');
        $table->addColumn('locale', 'string', ['length' => 10]);
        $table->addColumn('key', 'string', ['length' => 255]);
        $table->addColumn('value', 'text');
        $table->addColumn('created_at', 'datetime');
        $table->addColumn('updated_at', 'datetime');

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint('modules', ['module_id'], ['id'], ['onDelete' => 'CASCADE']);
        $table->addUniqueIndex(['module_id', 'locale', 'key']);
    }

    protected function rollback(): void
    {
        $this->dropTable('module_translations');
        $this->dropTable('module_views');
        $this->dropTable('module_migrations');
        $this->dropTable('module_entities');
        $this->dropTable('module_controllers');
        $this->dropTable('modules');
    }
}
