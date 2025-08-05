<?php

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contacts')]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected readonly int $id;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'contacts')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    private Customer $customer;

    #[ORM\Column(type: 'string', length: 255)]
    private string $type; // email, phone, meeting, note

    #[ORM\Column(type: 'string', length: 255)]
    private string $subject;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'datetime')]
    private DateTime $contact_date;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'completed'; // scheduled, completed, cancelled

    #[ORM\Column(type: 'datetime')]
    private DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    private DateTime $updated_at;

    public function __construct()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
        $this->contact_date = new DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getCustomer(): Customer { return $this->customer; }
    public function getType(): string { return $this->type; }
    public function getSubject(): string { return $this->subject; }
    public function getDescription(): ?string { return $this->description; }
    public function getContactDate(): DateTime { return $this->contact_date; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): DateTime { return $this->created_at; }
    public function getUpdatedAt(): DateTime { return $this->updated_at; }

    // Setters
    public function setCustomer(Customer $customer): self { $this->customer = $customer; return $this; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setContactDate(DateTime $contact_date): self { $this->contact_date = $contact_date; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updated_at = new DateTime();
    }
} 