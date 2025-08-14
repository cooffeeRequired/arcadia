<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class EmailTemplateSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM email_templates");

        $templates = [
            [
                'name' => 'Uvítací e-mail',
                'subject' => 'Vítejte v Arcadia CRM',
                'content' => '<h2>Vítejte v Arcadia CRM!</h2><p>Děkujeme za registraci do našeho CRM systému.</p><p>Váš tým Arcadia CRM</p>',
                'category' => 'welcome',
                'is_active' => 1,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ],
            [
                'name' => 'Follow-up e-mail',
                'subject' => 'Následný kontakt',
                'content' => '<h2>Následný kontakt</h2><p>Rádi bychom se zeptali, jak se Vám líbí naše služby.</p><p>S pozdravem,<br>Arcadia CRM</p>',
                'category' => 'follow_up',
                'is_active' => 1,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ],
            [
                'name' => 'Faktura',
                'subject' => 'Faktura č. {invoice_number}',
                'content' => '<h2>Faktura č. {invoice_number}</h2><p>V příloze naleznete fakturu za naše služby.</p><p>Děkujeme za Vaši důvěru.</p>',
                'category' => 'invoice',
                'is_active' => 1,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ],
            [
                'name' => 'Připomenutí',
                'subject' => 'Připomenutí: {reminder_text}',
                'content' => '<h2>Připomenutí</h2><p>{reminder_text}</p><p>S pozdravem,<br>Arcadia CRM</p>',
                'category' => 'reminder',
                'is_active' => 1,
                'user_id' => 2,
                'created_at' => '2025-08-08 01:15:01',
                'updated_at' => '2025-08-08 01:15:01'
            ]
        ];

        foreach ($templates as $template) {
            $builder
                ->insert('email_templates')
                ->values([
                    'name' => ':name',
                    'subject' => ':subject',
                    'content' => ':content',
                    'category' => ':category',
                    'is_active' => ':is_active',
                    'user_id' => ':user_id',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'name' => $template['name'],
                    'subject' => $template['subject'],
                    'content' => $template['content'],
                    'category' => $template['category'],
                    'is_active' => $template['is_active'],
                    'user_id' => $template['user_id'],
                    'created_at' => $template['created_at'],
                    'updated_at' => $template['updated_at']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($templates) . " e-mailových šablon\n";
    }
}
