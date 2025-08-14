<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Types;

class CreateModulesTable extends AbstractMigration
{
    protected function migrate(): void
    {
        $this->setDescription('Vytvoření tabulky pro správu modulů');

        $table = $this->createTable('modules');

        $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        $table->addColumn('name', Types::STRING, ['length' => 100, 'notnull' => true]);
        $table->addColumn('display_name', Types::STRING, ['length' => 255, 'notnull' => true]);
        $table->addColumn('description', Types::TEXT, ['notnull' => false]);
        $table->addColumn('version', Types::STRING, ['length' => 20, 'notnull' => true]);
        $table->addColumn('author', Types::STRING, ['length' => 255, 'notnull' => false]);
        $table->addColumn('is_enabled', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
        $table->addColumn('is_installed', Types::BOOLEAN, ['notnull' => true, 'default' => false]);
        $table->addColumn('install_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('update_date', Types::DATETIME_MUTABLE, ['notnull' => false]);
        $table->addColumn('dependencies', Types::JSON, ['notnull' => false]);
        $table->addColumn('settings', Types::JSON, ['notnull' => false]);
        $table->addColumn('permissions', Types::JSON, ['notnull' => false]);
        $table->addColumn('created_at', Types::DATETIME_MUTABLE, ['notnull' => true]);
        $table->addColumn('updated_at', Types::DATETIME_MUTABLE, ['notnull' => true]);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name'], 'UNIQ_MODULES_NAME');
    }

    protected function rollback(): void
    {
        $this->raw("DROP TABLE IF EXISTS `modules`");
    }
}
