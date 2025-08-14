<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EmailSignatureSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM email_signatures");

        $signatures = [
            [
                'name' => 'Standardní podpis',
                'content' => '<p>Děkuji za Vaši důvěru.</p><p>S pozdravem,<br>Arcadia CRM Team</p>',
                'is_default' => 1,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ],
            [
                'name' => 'Formální podpis',
                'content' => '<p>V případě jakýchkoliv dotazů nás neváhejte kontaktovat.</p><p>S úctou,<br>Arcadia CRM</p>',
                'is_default' => 0,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ]
        ];

        foreach ($signatures as $signature) {
            $builder
                ->insert('email_signatures')
                ->values([
                    'name' => ':name',
                    'content' => ':content',
                    'is_default' => ':is_default',
                    'user_id' => ':user_id',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'name' => $signature['name'],
                    'content' => $signature['content'],
                    'is_default' => $signature['is_default'],
                    'user_id' => $signature['user_id'],
                    'created_at' => $signature['created_at'],
                    'updated_at' => $signature['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($signatures) . " e-mailových podpisů\n";
    }
}
