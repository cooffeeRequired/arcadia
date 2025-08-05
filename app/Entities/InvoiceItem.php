<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_items')]
class InvoiceItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private $invoice;

    #[ORM\Column(type: 'string', length: 255)]
    private $description;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $quantity = 1;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $unit_price = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $total = 0;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private $unit = 'ks';

    public function getId() { return $this->id; }

    public function getInvoice() { return $this->invoice; }
    public function setInvoice($invoice) { $this->invoice = $invoice; }

    public function getDescription() { return $this->description; }
    public function setDescription($description) { $this->description = $description; }

    public function getQuantity() { return $this->quantity; }
    public function setQuantity($quantity) { $this->quantity = $quantity; }

    public function getUnitPrice() { return $this->unit_price; }
    public function setUnitPrice($unit_price) { $this->unit_price = $unit_price; }

    public function getTotal() { return $this->total; }
    public function setTotal($total) { $this->total = $total; }

    public function getUnit() { return $this->unit; }
    public function setUnit($unit) { $this->unit = $unit; }

    public function calculateTotal()
    {
        $this->total = $this->quantity * $this->unit_price;
        return $this->total;
    }
} 