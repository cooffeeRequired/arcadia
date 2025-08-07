<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'deals')]
class Deal implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'deals')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id')]
    private Customer $customer;

    #[ORM\Column(type: 'string', length: 255)]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true)]
    private ?float $value = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $stage = 'prospecting'; // prospecting, qualification, proposal, negotiation, closed_won, closed_lost

    #[ORM\Column(type: 'decimal', precision: 3, scale: 2)]
    private float $probability = 0.0; // 0.0 - 1.0

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $expected_close_date = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $status = 'active'; // active, won, lost, cancelled

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getCustomer(): Customer { return $this->customer; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getValue(): ?float { return $this->value; }
    public function getStage(): string { return $this->stage; }
    public function getProbability(): float { return $this->probability; }
    public function getExpectedCloseDate(): ?\DateTime { return $this->expected_close_date; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setCustomer(Customer $customer): self { $this->customer = $customer; return $this; }
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setValue(?float $value): self { $this->value = $value; return $this; }
    public function setStage(string $stage): self { $this->stage = $stage; return $this; }
    public function setProbability(float $probability): self { $this->probability = $probability; return $this; }
    public function setExpectedCloseDate(?\DateTime $expected_close_date): self { $this->expected_close_date = $expected_close_date; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'customer' => $this->getCustomer()->getId(),
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'value' => $this->getValue(),
            'stage' => $this->getStage(),
            'probability' => $this->getProbability(),
            'expected_close_date' => $this->getExpectedCloseDate()?->format('Y-m-d'),
            'status' => $this->getStatus(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
