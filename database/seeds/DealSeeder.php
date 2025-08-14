<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class DealSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM deals");

        $deals = [
            [
                'customer_id' => 5,
                'title' => 'Technická podpora',
                'description' => 'Roční technická podpora',
                'value' => 35000.00,
                'stage' => 'prospecting',
                'probability' => 0.20,
                'expected_close_date' => '2024-05-15',
                'status' => 'active',
                'created_at' => '2025-08-04 18:10:53',
                'updated_at' => '2025-08-04 18:10:53'
            ]
        ];

        foreach ($deals as $deal) {
            $builder
                ->insert('deals')
                ->values([
                    'customer_id' => ':customer_id',
                    'title' => ':title',
                    'description' => ':description',
                    'value' => ':value',
                    'stage' => ':stage',
                    'probability' => ':probability',
                    'expected_close_date' => ':expected_close_date',
                    'status' => ':status',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'customer_id' => $deal['customer_id'],
                    'title' => $deal['title'],
                    'description' => $deal['description'],
                    'value' => $deal['value'],
                    'stage' => $deal['stage'],
                    'probability' => $deal['probability'],
                    'expected_close_date' => $deal['expected_close_date'],
                    'status' => $deal['status'],
                    'created_at' => $deal['created_at'],
                    'updated_at' => $deal['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($deals) . " obchodů\n";
    }
}
