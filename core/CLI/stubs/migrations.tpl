<?php /** @noinspection PhpUnhandledExceptionInspection */

use Core\CLI\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Types\Types;

class {className} extends AbstractMigration
{
    protected function migrate(): void
    {
        // TODO: Implement your migration here.
        // Příklad:
        // $table = $this->createTable('users');
        // $table->addColumn('id', Types::INTEGER, ['autoincrement' => true]);
        // $table->addColumn('name', Types::STRING, ['length' => 255]);
        // $table->setPrimaryKey(['id']);

        // Nebo pro raw SQL:
        // $this->raw("INSERT INTO users (name) VALUES ('admin')");
    }

    protected function rollback(): void
    {
        // TODO: Implement your rollback here.
        // Příklad:
        // $this->dropTable('users');

        // Nebo pro raw SQL:
        // $this->raw("DELETE FROM users WHERE name = 'admin'");
    }
}
