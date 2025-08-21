<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Invoice;
use App\Entities\InvoiceItem;
use Core\Http\Response;
use Core\Render\BaseController;
use Core\Services\TableUI;
use Core\Services\HeaderUI;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class InvoiceController extends BaseController
{
    public function index(): Response\ViewResponse
    {
        $invoices = $this->em->getRepository(Invoice::class)->findAll();

        // Převod na asociativní pole pro TableUI
        $invoicesData = array_map(function($invoice) {
            return [
                'id' => $invoice->getId(),
                'invoice_number' => $invoice->getInvoiceNumber(),
                'customer_name' => $invoice->getCustomer() ? $invoice->getCustomer()->getName() : 'N/A',
                'issue_date' => $invoice->getIssueDate()->format('d.m.Y'),
                'due_date' => $invoice->getDueDate()->format('d.m.Y'),
                'total' => number_format($invoice->getTotal(), 0, ',', ' ') . ' ' . $invoice->getCurrency(),
                'status' => $invoice->getStatus(),
                'currency' => $invoice->getCurrency(),
                'items_count' => count($invoice->getItems())
            ];
        }, $invoices);

        // Vytvoření moderního headeru s HeaderUI komponentem
        $headerUI = new HeaderUI('invoices-header', [
            'title' => 'Seznam faktur',
            'icon' => 'fas fa-file-invoice',
            'subtitle' => 'Správa faktur a platebních dokladů'
        ]);

        // Přidání statistik
        $headerUI->setStats([
            'total' => [
                'label' => 'Celkem',
                'count' => count($invoices) . ' faktur',
                'type' => 'blue'
            ],
            'paid' => [
                'label' => 'Zaplacené',
                'count' => count(array_filter($invoices, fn($i) => $i->getStatus() === 'paid')) . ' faktur',
                'type' => 'green'
            ],
            'overdue' => [
                'label' => 'Po splatnosti',
                'count' => count(array_filter($invoices, fn($i) => $i->getDueDate() < new \DateTime() && $i->getStatus() !== 'paid')) . ' faktur',
                'type' => 'red'
            ],
            'total_amount' => [
                'label' => 'Celková částka',
                'count' => number_format(array_sum(array_map(fn($i) => $i->getTotal(), $invoices)), 0, ',', ' ') . ' Kč',
                'type' => 'purple'
            ]
        ]);

        // Přidání poslední aktualizace
        $headerUI->setLastUpdate('Poslední aktualizace: ' . date('d.m.Y H:i'));

        // Přidání tlačítek
        $headerUI->addButton(
            'create-invoice',
            '<i class="fas fa-plus mr-2"></i>Nová faktura',
            function() {
                return "window.location.href='/invoices/create'";
            },
            ['type' => 'primary']
        );

        // Vytvoření moderní tabulky s TableUI komponentem
        $tableUI = new TableUI('invoices', [
            'headers' => ['ID', 'Číslo faktury', 'Zákazník', 'Datum vystavení', 'Splatnost', 'Částka', 'Stav', 'Měna', 'Položky'],
            'data' => $invoicesData,
            'searchable' => true,
            'sortable' => true,
            'pagination' => true,
            'perPage' => 15,
            'title' => 'Seznam faktur',
            'icon' => 'fas fa-file-invoice',
            'emptyMessage' => 'Žádné faktury nebyly nalezeny',
            'search_controller' => 'App\\Controllers\\InvoiceController',
            'search_method' => 'ajaxSearch'
        ]);

        // Přidání sloupců
        $tableUI->addColumn('id', 'ID', ['sortable' => true])
                ->addColumn('invoice_number', 'Číslo faktury', ['sortable' => true])
                ->addColumn('customer_name', 'Zákazník', ['sortable' => true])
                ->addColumn('issue_date', 'Datum vystavení', ['sortable' => true, 'format' => 'date', 'position' => 'center'])
                ->addColumn('due_date', 'Splatnost', ['sortable' => true, 'format' => 'date', 'position' => 'center'])
                ->addColumn('total', 'Částka', ['sortable' => true, 'position' => 'right', 'format' => 'currency'])
                ->addColumn('status', 'Stav', ['sortable' => true, 'position' => 'center'])
                ->addColumn('currency', 'Měna', ['sortable' => true, 'position' => 'center'])
                ->addColumn('items_count', 'Položky', ['sortable' => true, 'position' => 'center']);

        // Přidání akcí pro řádky
        $tableUI->addAction('Zobrazit', function($params) {
            return "window.location.href='/invoices/' + {$params['row']}.id";
        }, ['type' => 'primary'])
        ->addAction('PDF', function($params) {
            return "window.open('/invoices/' + {$params['row']}.id + '/pdf', '_blank')";
        }, ['type' => 'success'])
        ->addAction('Upravit', function($params) {
            return "window.location.href='/invoices/' + {$params['row']}.id + '/edit'";
        }, ['type' => 'default'])
        ->addAction('Smazat', function($params) {
            return "if(confirm('Opravdu smazat fakturu?')) window.location.href='/invoices/' + {$params['row']}.id + '/delete'";
        }, ['type' => 'danger']);

        // Přidání vyhledávání
        $tableUI->addSearchPanel('Vyhledat fakturu...', function() {
            return "searchInvoices()";
        });

        // Přidání vlastních tlačítek
        $tableUI->addButtonToHeader(
            'export-invoices',
            '<i class="fas fa-download mr-2"></i>Export CSV',
            'pointer',
            function($params) {
                return "exportInvoices({$params['filteredData']})";
            },
            ['type' => 'success']
        );

        // Přidání hromadných akcí
        $tableUI->addBulkActions([
            'delete' => [
                'label' => 'Smazat vybrané',
                'icon' => 'fas fa-trash',
                'type' => 'danger',
                'callback' => function($params) {
                    return "if(confirm('Opravdu smazat vybrané faktury?')) deleteSelectedInvoices({$params['filteredData']})";
                }
            ],
            'export' => [
                'label' => 'Exportovat vybrané',
                'icon' => 'fas fa-download',
                'type' => 'primary',
                'callback' => function($params) {
                    return "exportSelectedInvoices({$params['filteredData']})";
                }
            ],
            'pdf' => [
                'label' => 'Stáhnout PDF',
                'icon' => 'fas fa-file-pdf',
                'type' => 'warning',
                'callback' => function($params) {
                    return "downloadSelectedInvoicesPDF({$params['filteredData']})";
                }
            ]
        ]);

        return $this->view('invoices.index', [
            'invoices' => $invoices,
            'invoicesData' => $invoicesData,
            'headerHTML' => $headerUI->render(),
            'tableHTML' => $tableUI->render(),
            'pagination' => (object) [
                'from' => 1,
                'to' => count($invoices),
                'total' => count($invoices),
                'currentPage' => 1,
                'lastPage' => 1
            ]
        ]);
    }

    public function show($id): Response\ViewResponse
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->notFound();
        }

        return $this->view('invoices.show', [
            'invoice' => $invoice
        ]);
    }

    public function create(): Response\ViewResponse
    {
        $customers = $this->em->getRepository(Customer::class)->findAll();

        return $this->view('invoices.create', [
            'customers' => $customers
        ]);
    }

    public function store(): void
    {
        $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id', 0));

        if (!$customer) {
            $this->redirect('/invoices');
        }

        $invoice = new Invoice();
        $invoice->setCustomer($customer);
        $invoice->setInvoiceNumber($this->generateInvoiceNumber());
        $invoice->setIssueDate(new \DateTime($this->input('issue_date', 'now')));
        $invoice->setDueDate(new \DateTime($this->input('due_date', '+30 days')));
        $invoice->setTaxRate($this->input('tax_rate') ? (float)$this->input('tax_rate') : 21);
        $invoice->setCurrency($this->input('currency', 'CZK'));
        $invoice->setNotes($this->input('notes', null));
        $invoice->setStatus($this->input('status', 'draft'));

        // Přidání položek
        if ($this->has('items') && is_array($this->input('items'))) {
            foreach ($this->input('items') as $itemData) {
                if (!empty($itemData['description'])) {
                    $item = new InvoiceItem();
                    $item->setDescription($itemData['description']);
                    $item->setQuantity($itemData['quantity'] ? (float)$itemData['quantity'] : 1);
                    $item->setUnitPrice($itemData['unit_price'] ? (float)$itemData['unit_price'] : 0);
                    $item->setUnit($itemData['unit'] ?? 'ks');
                    $item->calculateTotal();

                    $invoice->addItem($item);
                }
            }
        }

        $invoice->calculateTotals();

        $this->em->persist($invoice);
        $this->em->flush();

        $this->redirect('/invoices/' . $invoice->getId());
    }

    public function edit($id): Response\ViewResponse
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->notFound();
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();

        return $this->view('invoices.edit', [
            'invoice' => $invoice,
            'customers' => $customers
        ]);
    }

    public function update($id): void
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            $this->redirect('/invoices');
        }

        $customer = $this->em->getRepository(Customer::class)->find($this->input('customer_id', 0));

        if (!$customer) {
            $this->redirect('/invoices');
        }

        $invoice->setCustomer($customer);
        $invoice->setIssueDate(new \DateTime($this->input('issue_date', 'now')));
        $invoice->setDueDate(new \DateTime($this->input('due_date', '+30 days')));
        $invoice->setTaxRate($this->input('tax_rate') ? (float)$this->input('tax_rate') : 21);
        $invoice->setCurrency($this->input('currency', 'CZK'));
        $invoice->setNotes($this->input('notes', null));
        $invoice->setStatus($this->input('status', 'draft'));

        // Odstranění starých položek
        foreach ($invoice->getItems() as $item) {
            $this->em->remove($item);
        }

        // Přidání nových položek
        if ($this->has('items') && is_array($this->input('items'))) {
            foreach ($this->input('items') as $itemData) {
                if (!empty($itemData['description'])) {
                    $item = new InvoiceItem();
                    $item->setDescription($itemData['description']);
                    $item->setQuantity($itemData['quantity'] ? (float)$itemData['quantity'] : 1);
                    $item->setUnitPrice($itemData['unit_price'] ? (float)$itemData['unit_price'] : 0);
                    $item->setUnit($itemData['unit'] ?? 'ks');
                    $item->calculateTotal();

                    $invoice->addItem($item);
                }
            }
        }

        $invoice->calculateTotals();

        $this->em->flush();

        $this->redirect('/invoices/' . $invoice->getId());
    }

    public function delete($id): void
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            $this->redirect('/invoices');
        }

        $this->em->remove($invoice);
        $this->em->flush();

        $this->redirect('/invoices');
    }

    public function pdf($id): Response\AbstractResponse
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);

        if (!$invoice) {
            return $this->notFound('Faktura nebyla nalezena');
        }

        // Generování QR kódu pro platbu
        $qrData = $this->generatePaymentQRCode($invoice);

        // Vytvoření PDF
        $html = $this->generateInvoiceHTML($invoice, $qrData);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response\HtmlResponse($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="faktura-' . $invoice->getInvoiceNumber() . '.pdf"'
        ]);
    }

    private function generateInvoiceNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        $qb = $this->em->createQueryBuilder();
        $qb->select('COUNT(i.id)')
           ->from(Invoice::class, 'i')
           ->where('i.invoice_number LIKE :pattern')
           ->setParameter('pattern', "F{$year}{$month}%");

        $count = $qb->getQuery()->getSingleScalarResult();

        return sprintf('F%s%s%04d', $year, $month, $count + 1);
    }

    private function generatePaymentQRCode($invoice): string
    {
        // QR kód pro platbu (formát pro české banky)
        $qrData = [
            'account' => '123456789/0100', // IBAN
            'amount' => $invoice->getTotal(),
            'currency' => $invoice->getCurrency(),
            'message' => $invoice->getInvoiceNumber(),
            'variable_symbol' => $invoice->getId()
        ];

        $qrCode = new QrCode(json_encode($qrData));
        $qrCode->setSize(200);
        $qrCode->setMargin(10);

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return 'data:image/png;base64,' . base64_encode($result->getString());
    }

    private function generateInvoiceHTML($invoice, $qrCodeData): string
    {
        $customer = $invoice->getCustomer();

        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Faktura ' . $invoice->getInvoiceNumber() . '</title>
            <style>
                body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
                .header { text-align: center; margin-bottom: 30px; }
                .company-info { float: left; width: 45%; }
                .invoice-info { float: right; width: 45%; text-align: right; }
                .clear { clear: both; }
                .customer-info { margin: 20px 0; }
                table { width: 100%; border-collapse: collapse; margin: 20px 0; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f5f5f5; }
                .totals { float: right; width: 300px; }
                .qr-code { text-align: center; margin: 20px 0; }
                .qr-code img { max-width: 200px; }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>FAKTURA</h1>
                <h2>' . $invoice->getInvoiceNumber() . '</h2>
            </div>

            <div class="company-info">
                <h3>Dodavatel</h3>
                <p>Arcadia CRM<br>
                Václavská 1<br>
                110 00 Praha 1<br>
                IČ: 12345678<br>
                DIČ: CZ12345678</p>
            </div>

            <div class="invoice-info">
                <p><strong>Datum vystavení:</strong> ' . $invoice->getIssueDate()->format('d.m.Y') . '<br>
                <strong>Datum splatnosti:</strong> ' . $invoice->getDueDate()->format('d.m.Y') . '<br>
                <strong>Způsob platby:</strong> Bankovní převod</p>
            </div>

            <div class="clear"></div>

            <div class="customer-info">
                <h3>Odběratel</h3>
                <p>' . $customer->getName() . '<br>';

        if ($customer->getCompany()) {
            $html .= $customer->getCompany() . '<br>';
        }

        $html .= $customer->getAddress() . '<br>
                ' . $customer->getZipCode() . ' ' . $customer->getCity() . '<br>';

        if ($customer->getEmail()) {
            $html .= 'Email: ' . $customer->getEmail() . '<br>';
        }

        if ($customer->getPhone()) {
            $html .= 'Tel: ' . $customer->getPhone();
        }

        $html .= '</p>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Popis</th>
                        <th>Množství</th>
                        <th>Jednotka</th>
                        <th>Cena za jednotku</th>
                        <th>Celkem</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($invoice->getItems() as $item) {
            $html .= '<tr>
                        <td>' . htmlspecialchars($item->getDescription()) . '</td>
                        <td>' . $item->getQuantity() . '</td>
                        <td>' . $item->getUnit() . '</td>
                        <td>' . number_format($item->getUnitPrice(), 2, ',', ' ') . ' ' . $invoice->getCurrency() . '</td>
                        <td>' . number_format($item->getTotal(), 2, ',', ' ') . ' ' . $invoice->getCurrency() . '</td>
                    </tr>';
        }

        $html .= '</tbody>
            </table>

            <div class="totals">
                <p><strong>Mezisoučet:</strong> ' . number_format($invoice->getSubtotal(), 2, ',', ' ') . ' ' . $invoice->getCurrency() . '</p>
                <p><strong>DPH (' . $invoice->getTaxRate() . '%):</strong> ' . number_format($invoice->getTaxAmount(), 2, ',', ' ') . ' ' . $invoice->getCurrency() . '</p>
                <p><strong>Celkem k úhradě:</strong> ' . number_format($invoice->getTotal(), 2, ',', ' ') . ' ' . $invoice->getCurrency() . '</p>
            </div>

            <div class="clear"></div>

            <div class="qr-code">
                <h3>QR kód pro platbu</h3>
                <img src="' . $qrCodeData . '" alt="QR kód pro platbu">
            </div>

            <div style="margin-top: 30px;">
                <p><strong>Poznámky:</strong></p>
                <p>' . nl2br(htmlspecialchars($invoice->getNotes() ?? '')) . '</p>
            </div>
        </body>
        </html>';

        return $html;
    }

    /**
     * AJAX vyhledávání faktur
     */
    public function ajaxSearch(string $query): array
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('i', 'c')
           ->from(Invoice::class, 'i')
           ->leftJoin('i.customer', 'c')
           ->where('i.invoiceNumber LIKE :query OR i.status LIKE :query OR c.name LIKE :query')
           ->setParameter('query', '%' . $query . '%')
           ->orderBy('i.issueDate', 'DESC');

        $invoices = $qb->getQuery()->getResult();

        return array_map(function($invoice) {
            return [
                'id' => $invoice->getId(),
                'invoice_number' => $invoice->getInvoiceNumber(),
                'customer_name' => $invoice->getCustomer() ? $invoice->getCustomer()->getName() : 'N/A',
                'issue_date' => $invoice->getIssueDate()->format('d.m.Y'),
                'due_date' => $invoice->getDueDate()->format('d.m.Y'),
                'total' => number_format($invoice->getTotal(), 0, ',', ' ') . ' ' . $invoice->getCurrency(),
                'status' => $invoice->getStatus(),
                'currency' => $invoice->getCurrency(),
                'items_count' => count($invoice->getItems())
            ];
        }, $invoices);
    }
}
