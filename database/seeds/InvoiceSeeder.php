<?php /** @noinspection ALL */

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class InvoiceSeeder
{
    public function run(Connection $connection, QueryBuilder $builder): void
    {
        // Nejdříve smažeme existující data
        $connection->executeStatement("DELETE FROM invoice_items");
        $connection->executeStatement("DELETE FROM invoices");

        $invoices = [
            [
                'customer_id' => 5,
                'invoice_number' => 'F2025080001',
                'issue_date' => '2025-08-04',
                'due_date' => '2025-09-03',
                'subtotal' => 0.00,
                'tax_rate' => 21.00,
                'tax_amount' => 0.00,
                'total' => 0.00,
                'currency' => 'CZK',
                'notes' => '',
                'status' => 'draft',
                'created_at' => '2025-08-04 19:10:16',
                'updated_at' => '2025-08-04 19:10:16'
            ]
        ];

        foreach ($invoices as $invoice) {
            $builder
                ->insert('invoices')
                ->values([
                    'customer_id' => ':customer_id',
                    'invoice_number' => ':invoice_number',
                    'issue_date' => ':issue_date',
                    'due_date' => ':due_date',
                    'subtotal' => ':subtotal',
                    'tax_rate' => ':tax_rate',
                    'tax_amount' => ':tax_amount',
                    'total' => ':total',
                    'currency' => ':currency',
                    'notes' => ':notes',
                    'status' => ':status',
                    'created_at' => ':created_at',
                    'updated_at' => ':updated_at'
                ])
                ->setParameters([
                    'customer_id' => $invoice['customer_id'],
                    'invoice_number' => $invoice['invoice_number'],
                    'issue_date' => $invoice['issue_date'],
                    'due_date' => $invoice['due_date'],
                    'subtotal' => $invoice['subtotal'],
                    'tax_rate' => $invoice['tax_rate'],
                    'tax_amount' => $invoice['tax_amount'],
                    'total' => $invoice['total'],
                    'currency' => $invoice['currency'],
                    'notes' => $invoice['notes'],
                    'status' => $invoice['status'],
                    'created_at' => $invoice['created_at'],
                    'updated_at' => $invoice['updated_at']
                ]);

            $builder->executeStatement();
        }

        // Vytvoříme invoice items
        $invoiceItems = [
            [
                'invoice_id' => 1,
                'description' => 'Test',
                'quantity' => 1.00,
                'unit_price' => 0.00,
                'total' => 0.00,
                'unit' => 'ks'
            ],
            [
                'invoice_id' => 1,
                'description' => 'Test',
                'quantity' => 1.00,
                'unit_price' => 0.00,
                'total' => 0.00,
                'unit' => 'ks'
            ]
        ];

        foreach ($invoiceItems as $item) {
            $builder
                ->insert('invoice_items')
                ->values([
                    'invoice_id' => ':invoice_id',
                    'description' => ':description',
                    'quantity' => ':quantity',
                    'unit_price' => ':unit_price',
                    'total' => ':total',
                    'unit' => ':unit'
                ])
                ->setParameters([
                    'invoice_id' => $item['invoice_id'],
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $item['total'],
                    'unit' => $item['unit']
                ]);

            $builder->executeStatement();
        }

        echo "✅ Vytvořeno " . count($invoices) . " faktur a " . count($invoiceItems) . " položek\n";
    }
}
