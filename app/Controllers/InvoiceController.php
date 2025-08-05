<?php

namespace App\Controllers;

use App\Entities\Customer;
use App\Entities\Invoice;
use App\Entities\InvoiceItem;
use Core\Facades\Container;
use Core\Render\View;
use Doctrine\ORM\EntityManager;
use Dompdf\Dompdf;
use Dompdf\Options;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class InvoiceController
{
    private EntityManager $em;

    public function __construct()
    {
        $this->em = Container::get('doctrine.em');
    }

    public function index()
    {
        $invoices = $this->em->getRepository(Invoice::class)->findAll();
        
        $pagination = (object) [
            'from' => 1,
            'to' => count($invoices),
            'total' => count($invoices),
            'currentPage' => 1,
            'lastPage' => 1
        ];

        return View::render('invoices.index', [
            'invoices' => $invoices,
            'pagination' => $pagination
        ]);
    }

    public function show($id)
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);
        
        if (!$invoice) {
            http_response_code(404);
            return View::render('errors.404');
        }

        return View::render('invoices.show', [
            'invoice' => $invoice
        ]);
    }

    public function create()
    {
        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('invoices.create', [
            'customers' => $customers
        ]);
    }

    public function store()
    {
        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);
        
        if (!$customer) {
            http_response_code(400);
            return View::render('errors.400');
        }

        $invoice = new Invoice();
        $invoice->setCustomer($customer);
        $invoice->setInvoiceNumber($this->generateInvoiceNumber());
        $invoice->setIssueDate(new \DateTime($_POST['issue_date'] ?? 'now'));
        $invoice->setDueDate(new \DateTime($_POST['due_date'] ?? '+30 days'));
        $invoice->setTaxRate($_POST['tax_rate'] ? (float)$_POST['tax_rate'] : 21);
        $invoice->setCurrency($_POST['currency'] ?? 'CZK');
        $invoice->setNotes($_POST['notes'] ?? null);
        $invoice->setStatus($_POST['status'] ?? 'draft');

        // Přidání položek
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $itemData) {
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

        header('Location: /invoices/' . $invoice->getId());
        exit;
    }

    public function edit($id)
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);
        
        if (!$invoice) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $customers = $this->em->getRepository(Customer::class)->findAll();

        return View::render('invoices.edit', [
            'invoice' => $invoice,
            'customers' => $customers
        ]);
    }

    public function update($id)
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);
        
        if (!$invoice) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $customer = $this->em->getRepository(Customer::class)->find($_POST['customer_id'] ?? 0);
        
        if (!$customer) {
            http_response_code(400);
            return View::render('errors.400');
        }

        $invoice->setCustomer($customer);
        $invoice->setIssueDate(new \DateTime($_POST['issue_date'] ?? 'now'));
        $invoice->setDueDate(new \DateTime($_POST['due_date'] ?? '+30 days'));
        $invoice->setTaxRate($_POST['tax_rate'] ? (float)$_POST['tax_rate'] : 21);
        $invoice->setCurrency($_POST['currency'] ?? 'CZK');
        $invoice->setNotes($_POST['notes'] ?? null);
        $invoice->setStatus($_POST['status'] ?? 'draft');

        // Odstranění starých položek
        foreach ($invoice->getItems() as $item) {
            $this->em->remove($item);
        }

        // Přidání nových položek
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $itemData) {
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

        header('Location: /invoices/' . $invoice->getId());
        exit;
    }

    public function delete($id)
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);
        
        if (!$invoice) {
            http_response_code(404);
            return View::render('errors.404');
        }

        $this->em->remove($invoice);
        $this->em->flush();

        header('Location: /invoices');
        exit;
    }

    public function pdf($id)
    {
        $invoice = $this->em->getRepository(Invoice::class)->find($id);
        
        if (!$invoice) {
            http_response_code(404);
            return "Faktura nebyla nalezena";
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
        
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="faktura-' . $invoice->getInvoiceNumber() . '.pdf"');
        
        echo $dompdf->output();
        exit;
    }

    private function generateInvoiceNumber()
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

    private function generatePaymentQRCode($invoice)
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

    private function generateInvoiceHTML($invoice, $qrCodeData)
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
} 