<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'project_time_entries')]
class ProjectTimeEntry implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\Column(type: 'text', name: 'description')]
    private string $description;

    #[ORM\Column(type: 'datetime', name: 'start_time')]
    private \DateTime $startTime;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'end_time')]
    private ?\DateTime $endTime = null;

    #[ORM\Column(type: 'integer', nullable: true, name: 'duration_minutes')]
    private ?int $durationMinutes = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true, name: 'hourly_rate')]
    private ?float $hourlyRate = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2, nullable: true, name: 'total_cost')]
    private ?float $totalCost = null;

    #[ORM\Column(type: 'string', length: 50, name: 'status')]
    private string $status = 'running'; // running, stopped, billed

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'timeEntries')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true)]
    private ?User $user = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getDescription(): string { return $this->description; }
    public function getStartTime(): \DateTime { return $this->startTime; }
    public function getEndTime(): ?\DateTime { return $this->endTime; }
    public function getDurationMinutes(): ?int { return $this->durationMinutes; }
    public function getHourlyRate(): ?float { return $this->hourlyRate; }
    public function getTotalCost(): ?float { return $this->totalCost; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getProject(): Project { return $this->project; }
    public function getUser(): ?User { return $this->user; }

    // Setters
    public function setDescription(string $description): self { $this->description = $description; return $this; }
    public function setStartTime(\DateTime $startTime): self { $this->startTime = $startTime; return $this; }
    public function setEndTime(?\DateTime $endTime): self { $this->endTime = $endTime; return $this; }
    public function setDurationMinutes(?int $durationMinutes): self { $this->durationMinutes = $durationMinutes; return $this; }
    public function setHourlyRate(?float $hourlyRate): self { $this->hourlyRate = $hourlyRate; return $this; }
    public function setTotalCost(?float $totalCost): self { $this->totalCost = $totalCost; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setProject(Project $project): self { $this->project = $project; return $this; }
    public function setUser(?User $user): self { $this->user = $user; return $this; }

    // Helper methods
    public function getStatusColor(): string
    {
        return match($this->status) {
            'running' => 'green',
            'stopped' => 'yellow',
            'billed' => 'blue',
            default => 'gray'
        };
    }

    public function getFormattedDuration(): string
    {
        if (!$this->durationMinutes) {
            return '0m';
        }

        $hours = floor($this->durationMinutes / 60);
        $minutes = $this->durationMinutes % 60;

        if ($hours > 0) {
            return sprintf('%dh %dm', $hours, $minutes);
        }

        return sprintf('%dm', $minutes);
    }

    public function stop(): self
    {
        if ($this->status === 'running') {
            $this->endTime = new \DateTime();
            $this->status = 'stopped';
            $this->calculateDuration();
            $this->calculateCost();
        }

        return $this;
    }

    public function calculateDuration(): self
    {
        if ($this->startTime && $this->endTime) {
            $interval = $this->startTime->diff($this->endTime);
            $this->durationMinutes = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
        }

        return $this;
    }

    public function calculateCost(): self
    {
        if ($this->hourlyRate && $this->durationMinutes) {
            $this->totalCost = ($this->hourlyRate / 60) * $this->durationMinutes;
        }

        return $this;
    }

    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    public function isStopped(): bool
    {
        return $this->status === 'stopped';
    }

    public function isBilled(): bool
    {
        return $this->status === 'billed';
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
            'description' => $this->getDescription(),
            'start_time' => $this->getStartTime()->format('c'),
            'end_time' => $this->getEndTime()?->format('c'),
            'duration_minutes' => $this->getDurationMinutes(),
            'formatted_duration' => $this->getFormattedDuration(),
            'hourly_rate' => $this->getHourlyRate(),
            'total_cost' => $this->getTotalCost(),
            'status' => $this->getStatus(),
            'project_id' => $this->getProject()->getId(),
            'user_id' => $this->getUser()?->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
