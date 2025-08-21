<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'projects')]
class Project implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    protected int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'name')]
    protected string $name;

    #[ORM\Column(type: 'text', nullable: true, name: 'description')]
    protected ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, name: 'status')]
    protected string $status = 'active'; // active, completed, on_hold, cancelled

    #[ORM\Column(type: 'string', length: 20, name: 'priority')]
    protected string $priority = 'medium'; // low, medium, high, urgent

    #[ORM\Column(type: 'datetime', nullable: true, name: 'startDate')]
    protected ?\DateTime $startDate = null;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'endDate')]
    protected ?\DateTime $endDate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true, name: 'budget')]
    protected ?float $budget = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true, name: 'actualCost')]
    protected ?float $actualCost = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'estimatedHours')]
    protected ?int $estimatedHours = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'actualHours')]
    protected ?int $actualHours = null;

    #[ORM\Column(type: 'decimal', precision: 5, scale: 2, nullable: true, name: 'progress')]
    protected ?float $progress = 0.0;

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'miniPageSlug')]
    protected ?string $miniPageSlug = null;

    #[ORM\Column(type: 'text', nullable: true, name: 'miniPageContent')]
    protected ?string $miniPageContent = null;

    #[ORM\Column(type: 'json', nullable: true, name: 'settings')]
    protected ?array $settings = null;

    #[ORM\Column(type: 'datetime', name: 'createdAt')]
    protected \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updatedAt')]
    protected \DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'projects')]
    #[ORM\JoinColumn(name: 'customer_id', referencedColumnName: 'id', nullable: false)]
    protected Customer $customer;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'manager_id', referencedColumnName: 'id', nullable: true)]
    protected ?User $manager = null;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Invoice::class)]
    protected Collection $invoices;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: Activity::class)]
    protected Collection $activities;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectEvent::class, cascade: ['persist', 'remove'])]
    protected Collection $events;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectTimeEntry::class, cascade: ['persist', 'remove'])]
    protected Collection $timeEntries;

    #[ORM\OneToMany(mappedBy: 'project', targetEntity: ProjectFile::class, cascade: ['persist', 'remove'])]
    protected Collection $files;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        $this->invoices = new ArrayCollection();
        $this->activities = new ArrayCollection();
        $this->events = new ArrayCollection();
        $this->timeEntries = new ArrayCollection();
        $this->files = new ArrayCollection();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getPriority(): string { return $this->priority; }
    public function getStartDate(): ?\DateTime { return $this->startDate; }
    public function getEndDate(): ?\DateTime { return $this->endDate; }
    public function getBudget(): ?float { return $this->budget; }
    public function getActualCost(): ?float { return $this->actualCost; }
    public function getEstimatedHours(): ?int { return $this->estimatedHours; }
    public function getActualHours(): ?int { return $this->actualHours; }
    public function getProgress(): ?float { return $this->progress; }
    public function getMiniPageSlug(): ?string { return $this->miniPageSlug; }
    public function getMiniPageContent(): ?string { return $this->miniPageContent; }
    public function getSettings(): ?array { return $this->settings; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getCustomer(): Customer { return $this->customer; }
    public function getManager(): ?User { return $this->manager; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setPriority(string $priority): self { $this->priority = $priority; return $this; }
    public function setStartDate(?\DateTime $startDate): self { $this->startDate = $startDate; return $this; }
    public function setEndDate(?\DateTime $endDate): self { $this->endDate = $endDate; return $this; }
    public function setBudget(?float $budget): self { $this->budget = $budget; return $this; }
    public function setActualCost(?float $actualCost): self { $this->actualCost = $actualCost; return $this; }
    public function setEstimatedHours(?int $estimatedHours): self { $this->estimatedHours = $estimatedHours; return $this; }
    public function setActualHours(?int $actualHours): self { $this->actualHours = $actualHours; return $this; }
    public function setProgress(?float $progress): self { $this->progress = $progress; return $this; }
    public function setMiniPageSlug(?string $miniPageSlug): self { $this->miniPageSlug = $miniPageSlug; return $this; }
    public function setMiniPageContent(?string $miniPageContent): self { $this->miniPageContent = $miniPageContent; return $this; }
    public function setSettings(?array $settings): self { $this->settings = $settings; return $this; }
    public function setCustomer(Customer $customer): self { $this->customer = $customer; return $this; }
    public function setManager(?User $manager): self { $this->manager = $manager; return $this; }

    // Collections
    public function getInvoices(): Collection { return $this->invoices; }
    public function getActivities(): Collection { return $this->activities; }
    public function getEvents(): Collection { return $this->events; }
    public function getTimeEntries(): Collection { return $this->timeEntries; }
    public function getFiles(): Collection { return $this->files; }

    // Helper methods
    public function getStatusColor(): string
    {
        return match($this->status) {
            'active' => 'green',
            'completed' => 'blue',
            'on_hold' => 'yellow',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function getPriorityColor(): string
    {
        return match($this->priority) {
            'low' => 'green',
            'medium' => 'yellow',
            'high' => 'orange',
            'urgent' => 'red',
            default => 'gray'
        };
    }

    public function calculateProgress(): float
    {
        if ($this->estimatedHours && $this->actualHours) {
            return min(100, ($this->actualHours / $this->estimatedHours) * 100);
        }
        return $this->progress ?? 0.0;
    }

    public function calculateBudgetUtilization(): float
    {
        if ($this->budget && $this->actualCost) {
            return ($this->actualCost / $this->budget) * 100;
        }
        return 0.0;
    }

    public function isOverdue(): bool
    {
        if (!$this->endDate) {
            return false;
        }
        return $this->endDate < new \DateTime() && $this->status !== 'completed';
    }

    public function getDaysRemaining(): ?int
    {
        if (!$this->endDate) {
            return null;
        }
        $now = new \DateTime();
        $diff = $this->endDate->diff($now);
        return $diff->invert ? $diff->days : -$diff->days;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'status' => $this->getStatus(),
            'priority' => $this->getPriority(),
            'start_date' => $this->getStartDate()?->format('Y-m-d'),
            'end_date' => $this->getEndDate()?->format('Y-m-d'),
            'budget' => $this->getBudget(),
            'actual_cost' => $this->getActualCost(),
            'estimated_hours' => $this->getEstimatedHours(),
            'actual_hours' => $this->getActualHours(),
            'progress' => $this->getProgress(),
            'mini_page_slug' => $this->getMiniPageSlug(),
            'customer_id' => $this->getCustomer()->getId(),
            'manager_id' => $this->getManager()?->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
