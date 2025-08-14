<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class ActivitySeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM activities");

        $activities = [
            [
                'type' => 'contact',
                'title' => 'Nový kontakt: Technická konzultace',
                'description' => 'Kontakt typu: phone',
                'customer_id' => 5,
                'deal_id' => null,
                'contact_id' => 6,
                'created_at' => '2025-08-07 05:20:44',
                'updated_at' => '2025-08-07 05:20:44'
            ],
            [
                'type' => 'deal',
                'title' => 'Nový obchod: Technická podpora',
                'description' => 'Hodnota: 35,000 Kč - Pravděpodobnost: 20.00%',
                'customer_id' => 5,
                'deal_id' => 4,
                'contact_id' => null,
                'created_at' => '2025-08-07 05:20:44',
                'updated_at' => '2025-08-07 05:20:44'
            ],
            [
                'type' => 'customer',
                'title' => 'Nový zákazník: Tech Solutions s.r.o.',
                'description' => 'Společnost: Tech Solutions s.r.o.',
                'customer_id' => 5,
                'deal_id' => null,
                'contact_id' => null,
                'created_at' => '2025-08-07 05:20:44',
                'updated_at' => '2025-08-07 05:20:44'
            ],
            [
                'type' => 'customer',
                'title' => 'Nový zákazník: Test',
                'description' => 'Společnost: Test',
                'customer_id' => 6,
                'deal_id' => null,
                'contact_id' => null,
                'created_at' => '2025-08-07 05:20:44',
                'updated_at' => '2025-08-07 05:20:44'
            ]
        ];

        foreach ($activities as $activity) {
            $builder
                ->insert('activities')
                ->values([
                    'type' => ':type',
                    'title' => ':title',
                    'description' => ':description',
                    'customer_id' => ':customer_id',
                    'deal_id' => ':deal_id',
                    'contact_id' => ':contact_id',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'type' => $activity['type'],
                    'title' => $activity['title'],
                    'description' => $activity['description'],
                    'customer_id' => $activity['customer_id'],
                    'deal_id' => $activity['deal_id'],
                    'contact_id' => $activity['contact_id'],
                    'created_at' => $activity['created_at'],
                    'updated_at' => $activity['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($activities) . " aktivit\n";
    }
}
