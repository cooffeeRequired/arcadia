<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EmailServerSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM email_servers");

        $servers = [
            [
                'name' => 'Gmail SMTP',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'your-email@gmail.com',
                'password' => 'your-app-password',
                'from_email' => 'your-email@gmail.com',
                'from_name' => 'Arcadia CRM',
                'is_active' => 1,
                'is_default' => 1,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ],
            [
                'name' => 'Outlook SMTP',
                'host' => 'smtp-mail.outlook.com',
                'port' => 587,
                'encryption' => 'tls',
                'username' => 'your-email@outlook.com',
                'password' => 'your-password',
                'from_email' => 'your-email@outlook.com',
                'from_name' => 'Arcadia CRM',
                'is_active' => 1,
                'is_default' => 0,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ]
        ];

        foreach ($servers as $server) {
            $builder
                ->insert('email_servers')
                ->values([
                    'name' => ':name',
                    'host' => ':host',
                    'port' => ':port',
                    'encryption' => ':encryption',
                    'username' => ':username',
                    'password' => ':password',
                    'from_email' => ':from_email',
                    'from_name' => ':from_name',
                    'is_active' => ':is_active',
                    'is_default' => ':is_default',
                    'user_id' => ':user_id',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'name' => $server['name'],
                    'host' => $server['host'],
                    'port' => $server['port'],
                    'encryption' => $server['encryption'],
                    'username' => $server['username'],
                    'password' => $server['password'],
                    'from_email' => $server['from_email'],
                    'from_name' => $server['from_name'],
                    'is_active' => $server['is_active'],
                    'is_default' => $server['is_default'],
                    'user_id' => $server['user_id'],
                    'created_at' => $server['created_at'],
                    'updated_at' => $server['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($servers) . " e-mailových serverů\n";
    }
}
