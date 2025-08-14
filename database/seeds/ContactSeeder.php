<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class ContactSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM contacts");

        $contacts = [
            [
                'customer_id' => 1,
                'type' => 'email',
                'subject' => 'Nabídka produktů',
                'description' => 'Odeslána nabídka na CRM systém',
                'contact_date' => '2025-08-02 18:10:53',
                'status' => 'completed',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'customer_id' => 1,
                'type' => 'phone',
                'subject' => 'Kontrola nabídky',
                'description' => 'Zákazník má zájem o demo',
                'contact_date' => '2025-08-03 18:10:53',
                'status' => 'completed',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'customer_id' => 2,
                'type' => 'meeting',
                'subject' => 'Prezentace produktů',
                'description' => 'Schůzka v kanceláři',
                'contact_date' => '2025-08-01 18:10:53',
                'status' => 'completed',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'customer_id' => 3,
                'type' => 'email',
                'subject' => 'Partnerská spolupráce',
                'description' => 'Návrh na spolupráci',
                'contact_date' => '2025-07-30 18:10:53',
                'status' => 'completed',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'customer_id' => 4,
                'type' => 'phone',
                'subject' => 'První kontakt',
                'description' => 'Zájem o informace',
                'contact_date' => '2025-08-03 18:10:53',
                'status' => 'completed',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'customer_id' => 5,
                'type' => 'phone',
                'subject' => 'Technická konzultace',
                'description' => 'Diskuse o implementaci',
                'contact_date' => '2025-08-06 18:10:00',
                'status' => 'completed',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ],
            [
                'customer_id' => 1,
                'type' => 'email',
                'subject' => 'Nabídka produktů',
                'description' => 'Odeslána nabídka na CRM systém',
                'contact_date' => '2025-08-06 12:25:03',
                'status' => 'completed',
                'created_at' => '2025-08-08 12:25:03',
                'updated_at' => '2025-08-08 12:25:03'
            ],
            [
                'customer_id' => 1,
                'type' => 'phone',
                'subject' => 'Kontrola nabídky',
                'description' => 'Zákazník má zájem o demo',
                'contact_date' => '2025-08-07 12:25:03',
                'status' => 'completed',
                'created_at' => '2025-08-08 12:25:03',
                'updated_at' => '2025-08-08 12:25:03'
            ],
            [
                'customer_id' => 2,
                'type' => 'meeting',
                'subject' => 'Prezentace produktů',
                'description' => 'Schůzka v kanceláři',
                'contact_date' => '2025-08-05 12:25:03',
                'status' => 'completed',
                'created_at' => '2025-08-08 12:25:03',
                'updated_at' => '2025-08-08 12:25:03'
            ],
            [
                'customer_id' => 3,
                'type' => 'email',
                'subject' => 'Partnerská spolupráce',
                'description' => 'Návrh na spolupráci',
                'contact_date' => '2025-08-03 12:25:03',
                'status' => 'completed',
                'created_at' => '2025-08-08 12:25:03',
                'updated_at' => '2025-08-08 12:25:03'
            ],
            [
                'customer_id' => 4,
                'type' => 'phone',
                'subject' => 'První kontakt',
                'description' => 'Zájem o informace',
                'contact_date' => '2025-08-07 12:25:03',
                'status' => 'completed',
                'created_at' => '2025-08-08 12:25:03',
                'updated_at' => '2025-08-08 12:25:03'
            ],
            [
                'customer_id' => 5,
                'type' => 'meeting',
                'subject' => 'Technická konzultace',
                'description' => 'Diskuse o implementaci',
                'contact_date' => '2025-08-10 12:25:03',
                'status' => 'scheduled',
                'created_at' => '2025-08-08 12:25:03',
                'updated_at' => '2025-08-08 12:25:03'
            ]
        ];

        foreach ($contacts as $contact) {
            $builder
                ->insert('contacts')
                ->values([
                    'customer_id' => ':customer_id',
                    'type' => ':type',
                    'subject' => ':subject',
                    'description' => ':description',
                    'contact_date' => ':contact_date',
                    'status' => ':status',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'customer_id' => $contact['customer_id'],
                    'type' => $contact['type'],
                    'subject' => $contact['subject'],
                    'description' => $contact['description'],
                    'contact_date' => $contact['contact_date'],
                    'status' => $contact['status'],
                    'created_at' => $contact['created_at'],
                    'updated_at' => $contact['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($contacts) . " kontaktů\n";
    }
}
