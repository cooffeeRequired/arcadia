<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class UserSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        dd(password_hash('admin123', PASSWORD_DEFAULT));

        $user = new \App\Entities\User();

        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM users");
        
        $users = [
            [
                'name' => 'Manager User',
                'email' => 'manager@arcadia.cz',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'role' => 'manager',
                'avatar' => null,
                'is_active' => 1,
                'last_login' => null,
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'name' => 'Sales User',
                'email' => 'sales@arcadia.cz',
                'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
                'role' => 'user',
                'avatar' => null,
                'is_active' => 1,
                'last_login' => null,
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'name' => 'Administrátor',
                'email' => 'admin@arcadia.cz',
                'password' => password_hash('admin123', PASSWORD_DEFAULT), // admin123
                'role' => 'admin',
                'avatar' => null,
                'is_active' => 1,
                'last_login' => '2025-08-08 00:46:26',
                'created_at' => '2025-08-04 19:33:45',
                'updated_at' => '2025-08-04 19:33:45'
            ],
            [
                'name' => 'Admin User',
                'email' => 'admin@arcadia-crm.com',
                'password' => '$2y$12$iewvrfHP3G2VW6fSbfqeYeKCh9WZa8K4idYVmQ8n58N/dwKleUr7m', // admin123
                'role' => 'admin',
                'avatar' => null,
                'is_active' => 1,
                'last_login' => null,
                'created_at' => '2025-08-08 00:30:41',
                'updated_at' => '2025-08-08 00:30:41'
            ],
            [
                'name' => 'Manager User',
                'email' => 'manager@arcadia-crm.com',
                'password' => '$2y$12$ZFmkp7aO9eO96T42jKJryeu16eaUfep6Mjcr.GfINtuu.PL003PxS', // manager123
                'role' => 'manager',
                'avatar' => null,
                'is_active' => 1,
                'last_login' => null,
                'created_at' => '2025-08-08 00:30:42',
                'updated_at' => '2025-08-08 00:30:42'
            ],
            [
                'name' => 'Regular User',
                'email' => 'user@arcadia-crm.com',
                'password' => '$2y$12$bpIYLKCRYABMe1DeUnjSheWTzwmAaukM83Bpw/FQpZi5pQlVB6naC', // user123
                'role' => 'user',
                'avatar' => null,
                'is_active' => 1,
                'last_login' => null,
                'created_at' => '2025-08-08 00:30:42',
                'updated_at' => '2025-08-08 00:30:42'
            ]
        ];

        foreach ($users as $user) {
            $builder
                ->insert('users')
                ->values([
                    'name' => ':name',
                    'email' => ':email',
                    'password' => ':password',
                    'role' => ':role',
                    'avatar' => ':avatar',
                    'is_active' => ':is_active',
                    'last_login' => ':last_login',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                    'avatar' => $user['avatar'],
                    'is_active' => $user['is_active'],
                    'last_login' => $user['last_login'],
                    'created_at' => $user['created_at'],
                    'updated_at' => $user['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($users) . " uživatelů\n";
    }
}


