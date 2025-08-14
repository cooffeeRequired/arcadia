<?php

namespace App\Entities;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'activities')]
class Activity implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id', type: 'integer')]
    protected readonly int $id;

    #[ORM\Column(name: 'type', type: 'string', length: 50)]
    protected string $type;

    #[ORM\Column(name: 'title', type: 'string', length: 255)]
    protected string $title;

    #[ORM\Column(name: 'description', type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\ManyToOne(targetEntity: Customer::class)]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Customer $customer = null;

    #[ORM\ManyToOne(targetEntity: Deal::class)]
    #[ORM\JoinColumn(name: 'deal_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Deal $deal = null;

    #[ORM\ManyToOne(targetEntity: Contact::class)]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Contact $contact = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected DateTime $created_at;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    protected DateTime $updated_at;

    public function __construct()
    {
        $this->created_at = new DateTime();
        $this->updated_at = new DateTime();
    }

    // Gettery a Settery
    public function getId(): ?int {return $this->id;}

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getCustomer(): ?Customer
    {
        return $this->customer;
    }

    public function setCustomer(?Customer $customer): self
    {
        $this->customer = $customer;
        return $this;
    }

    public function getDeal(): ?Deal
    {
        return $this->deal;
    }

    public function setDeal(?Deal $deal): self
    {
        $this->deal = $deal;
        return $this;
    }

    public function getContact(): ?Contact
    {
        return $this->contact;
    }

    public function setContact(?Contact $contact): self
    {
        $this->contact = $contact;
        return $this;
    }

    public function getCreatedAt(): Carbon
    {
        return Carbon::instance($this->created_at);
    }

    public function getUpdatedAt(): Carbon
    {
        return Carbon::instance($this->updated_at);
    }

    public function updateTimestamp(): void
    {
        $this->updated_at = new DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'type' => $this->getType(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'customer' => $this->getCustomer()?->getId(),
            'deal' => $this->getDeal()?->getId(),
            'contact' => $this->getContact()?->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c'),
        ];
    }
}
