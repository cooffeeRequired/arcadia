<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'invoices')]
#[ORM\HasLifecycleCallbacks]
class Invoice implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private $id;

    #[ORM\Column(type: 'string', length: 50, unique: true, name: 'invoice_number')]
    private $invoice_number;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(nullable: false, name: 'customer_id')]
    private $customer;

    #[ORM\Column(type: 'date', name: 'issue_date')]
    private $issue_date;

    #[ORM\Column(type: 'date', name: 'due_date')]
    private $due_date;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, name: 'subtotal')]
    private $subtotal = 0;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, name: 'tax_rate')]
    private $tax_rate = 21;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, name: 'tax_amount')]
    private $tax_amount = 0;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, name: 'total')]
    private $total = 0;

    #[ORM\Column(type: 'string', length: 20, name: 'currency')]
    private $currency = 'CZK';

    #[ORM\Column(type: 'text', nullable: true, name: 'notes')]
    private $notes;

    #[ORM\Column(type: 'string', length: 20, name: 'status')]
    private $status = 'draft';

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private $created_at;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    private $updated_at;

    #[ORM\OneToMany(targetEntity: InvoiceItem::class, mappedBy: 'invoice', cascade: ['persist', 'remove'])]
    private $items;

    public function __construct()
    {
        $this->items = new \Doctrine\Common\Collections\ArrayCollection();
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt()
    {
        $this->updated_at = new \DateTime();
    }

    public function getId() { return $this->id; }

    public function getInvoiceNumber() { return $this->invoice_number; }
    public function setInvoiceNumber($invoice_number) { $this->invoice_number = $invoice_number; }

    public function getCustomer() { return $this->customer; }
    public function setCustomer($customer) { $this->customer = $customer; }

    public function getIssueDate() { return $this->issue_date; }
    public function setIssueDate($issue_date) { $this->issue_date = $issue_date; }

    public function getDueDate() { return $this->due_date; }
    public function setDueDate($due_date) { $this->due_date = $due_date; }

    public function getSubtotal() { return $this->subtotal; }
    public function setSubtotal($subtotal) { $this->subtotal = $subtotal; }

    public function getTaxRate() { return $this->tax_rate; }
    public function setTaxRate($tax_rate) { $this->tax_rate = $tax_rate; }

    public function getTaxAmount() { return $this->tax_amount; }
    public function setTaxAmount($tax_amount) { $this->tax_amount = $tax_amount; }

    public function getTotal() { return $this->total; }
    public function setTotal($total) { $this->total = $total; }

    public function getCurrency() { return $this->currency; }
    public function setCurrency($currency) { $this->currency = $currency; }

    public function getNotes() { return $this->notes; }
    public function setNotes($notes) { $this->notes = $notes; }

    public function getStatus() { return $this->status; }
    public function setStatus($status) { $this->status = $status; }

    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    public function getItems() { return $this->items; }

    public function addItem(InvoiceItem $item)
    {
        if (!$this->items->contains($item)) {
            $item->setInvoice($this);
            $this->items->add($item);
        }
        return $this;
    }

    public function removeItem(InvoiceItem $item)
    {
        if ($this->items->removeElement($item)) {
            if ($item->getInvoice() === $this) {
                $item->setInvoice(null);
            }
        }
        return $this;
    }

    public function calculateTotals()
    {
        $subtotal = 0;
        foreach ($this->items as $item) {
            $subtotal += $item->getQuantity() * $item->getUnitPrice();
        }

        $this->subtotal = $subtotal;
        $this->tax_amount = $subtotal * ($this->tax_rate / 100);
        $this->total = $subtotal + $this->tax_amount;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'invoice_number' => $this->getInvoiceNumber(),
            'customer' => $this->getCustomer()->getId(),
            'issue_date' => $this->getIssueDate()->format('Y-m-d'),
            'due_date' => $this->getDueDate()->format('Y-m-d'),
            'subtotal' => $this->getSubtotal(),
            'tax_rate' => $this->getTaxRate(),
            'tax_amount' => $this->getTaxAmount(),
            'total' => $this->getTotal(),
            'currency' => $this->getCurrency(),
            'notes' => $this->getNotes(),
            'status' => $this->getStatus(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c'),
            'items' => array_map(fn($item) => $item->jsonSerialize(), $this->getItems()->toArray())
        ];
    }
}
