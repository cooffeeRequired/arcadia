<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class CustomerSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data v opačném pořadí kvůli foreign keys
        $connection->executeStatement("DELETE FROM activities");
        $connection->executeStatement("DELETE FROM contacts");
        $connection->executeStatement("DELETE FROM deals");
        $connection->executeStatement("DELETE FROM invoice_items");
        $connection->executeStatement("DELETE FROM invoices");
        $connection->executeStatement("DELETE FROM customers");

        $customers = [
            [
                'name' => 'Tech Solutions s.r.o.',
                'email' => 'info@techsolutions.cz',
                'phone' => '+420 222 333 444',
                'company' => 'Tech Solutions s.r.o.',
                'category' => 'company',
                'address' => 'Dlouhá 45',
                'zip_code' => '11000',
                'city' => 'Praha',
                'country' => 'Česká republika',
                'status' => 'active',
                'notes' => 'IT společnost',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'name' => 'Test',
                'email' => 'Test@test.com',
                'phone' => 'Test',
                'company' => 'Test',
                'category' => 'company',
                'address' => 'TTTT',
                'zip_code' => null,
                'city' => null,
                'country' => null,
                'status' => 'active',
                'notes' => 'TTT',
                'created_at' => '2025-08-05 19:15:09',
                'updated_at' => '2025-08-05 19:15:09'
            ],
            [
                'name' => 'Jan Novák',
                'email' => 'jan.novak@email.cz',
                'phone' => '+420 123 456 789',
                'company' => 'Novák s.r.o.',
                'category' => 'company',
                'address' => 'Václavská 1',
                'zip_code' => '11000',
                'city' => 'Praha',
                'country' => 'Česká republika',
                'status' => 'active',
                'notes' => 'Důležitý klient',
                'created_at' => '2025-08-08 12:25:04',
                'updated_at' => '2025-08-08 12:25:04'
            ],
            [
                'name' => 'Marie Svobodová',
                'email' => 'marie.svobodova@email.cz',
                'phone' => '+420 987 654 321',
                'company' => null,
                'category' => 'person',
                'address' => 'Nerudova 15',
                'zip_code' => '11800',
                'city' => 'Praha',
                'country' => 'Česká republika',
                'status' => 'active',
                'notes' => 'Zájem o nové produkty',
                'created_at' => '2025-08-08 12:25:04',
                'updated_at' => '2025-08-08 12:25:04'
            ],
            [
                'name' => 'Petr Černý',
                'email' => 'petr.cerny@firma.cz',
                'phone' => '+420 555 123 456',
                'company' => 'Černý a spol.',
                'category' => 'company',
                'address' => 'Národní 25',
                'zip_code' => '11000',
                'city' => 'Praha',
                'country' => 'Česká republika',
                'status' => 'active',
                'notes' => 'Potenciální partner',
                'created_at' => '2025-08-08 12:25:04',
                'updated_at' => '2025-08-08 12:25:04'
            ],
            [
                'name' => 'Anna Dvořáková',
                'email' => 'anna.dvorakova@email.cz',
                'phone' => '+420 777 888 999',
                'company' => null,
                'category' => 'person',
                'address' => 'Karlova 8',
                'zip_code' => '12000',
                'city' => 'Praha',
                'country' => 'Česká republika',
                'status' => 'prospect',
                'notes' => 'Nový lead',
                'created_at' => '2025-08-08 12:25:04',
                'updated_at' => '2025-08-08 12:25:04'
            ]
        ];

        foreach ($customers as $customer) {
            $builder
                ->insert('customers')
                ->values([
                    'name' => ':name',
                    'email' => ':email',
                    'phone' => ':phone',
                    'company' => ':company',
                    'category' => ':category',
                    'address' => ':address',
                    'zip_code' => ':zip_code',
                    'city' => ':city',
                    'country' => ':country',
                    'status' => ':status',
                    'notes' => ':notes',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'name' => $customer['name'],
                    'email' => $customer['email'],
                    'phone' => $customer['phone'],
                    'company' => $customer['company'],
                    'category' => $customer['category'],
                    'address' => $customer['address'],
                    'zip_code' => $customer['zip_code'],
                    'city' => $customer['city'],
                    'country' => $customer['country'],
                    'status' => $customer['status'],
                    'notes' => $customer['notes'],
                    'created_at' => $customer['created_at'],
                    'updated_at' => $customer['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($customers) . " zákazníků\n";
    }
}
