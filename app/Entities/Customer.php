<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'customers')]
class Customer
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $company = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $category = 'person'; // person, company

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $address = null;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $zip_code = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $city = null;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $country = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $status = 'active'; // active, inactive, prospect

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $notes = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updated_at;

    #[ORM\OneToMany(targetEntity: Contact::class, mappedBy: 'customer')]
    private $contacts;

    #[ORM\OneToMany(targetEntity: Deal::class, mappedBy: 'customer')]
    private $deals;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->contacts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->deals = new \Doctrine\Common\Collections\ArrayCollection();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmail(): ?string { return $this->email; }
    public function getPhone(): ?string { return $this->phone; }
    public function getCompany(): ?string { return $this->company; }
    public function getCategory(): string { return $this->category; }
    public function getAddress(): ?string { return $this->address; }
    public function getZipCode(): ?string { return $this->zip_code; }
    public function getCity(): ?string { return $this->city; }
    public function getCountry(): ?string { return $this->country; }
    public function getStatus(): ?string { return $this->status; }
    public function getNotes(): ?string { return $this->notes; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }
    public function getContacts() { return $this->contacts; }
    public function getDeals() { return $this->deals; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setEmail(?string $email): self { $this->email = $email; return $this; }
    public function setPhone(?string $phone): self { $this->phone = $phone; return $this; }
    public function setCompany(?string $company): self { $this->company = $company; return $this; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }
    public function setAddress(?string $address): self { $this->address = $address; return $this; }
    public function setZipCode(?string $zip_code): self { $this->zip_code = $zip_code; return $this; }
    public function setCity(?string $city): self { $this->city = $city; return $this; }
    public function setCountry(?string $country): self { $this->country = $country; return $this; }
    public function setStatus(?string $status): self { $this->status = $status; return $this; }
    public function setNotes(?string $notes): self { $this->notes = $notes; return $this; }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updated_at = new \DateTime();
    }
} 