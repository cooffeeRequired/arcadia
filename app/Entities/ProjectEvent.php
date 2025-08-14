<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'project_events')]
class ProjectEvent implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'title')]
    private string $title;

    #[ORM\Column(type: 'text', nullable: true, name: 'description')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, name: 'type')]
    private string $type = 'milestone'; // milestone, meeting, deadline, task

    #[ORM\Column(type: 'string', length: 50, name: 'status')]
    private string $status = 'pending'; // pending, completed, cancelled

    #[ORM\Column(type: 'datetime', name: 'event_date')]
    private \DateTime $eventDate;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'completed_at')]
    private ?\DateTime $completedAt = null;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'events')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    private ?User $createdBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): ?string { return $this->description; }
    public function getType(): string { return $this->type; }
    public function getStatus(): string { return $this->status; }
    public function getEventDate(): \DateTime { return $this->eventDate; }
    public function getCompletedAt(): ?\DateTime { return $this->completedAt; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getProject(): Project { return $this->project; }
    public function getCreatedBy(): ?User { return $this->createdBy; }

    // Setters
    public function setTitle(string $title): self { $this->title = $title; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setType(string $type): self { $this->type = $type; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setEventDate(\DateTime $eventDate): self { $this->eventDate = $eventDate; return $this; }
    public function setCompletedAt(?\DateTime $completedAt): self { $this->completedAt = $completedAt; return $this; }
    public function setProject(Project $project): self { $this->project = $project; return $this; }
    public function setCreatedBy(?User $createdBy): self { $this->createdBy = $createdBy; return $this; }

    // Helper methods
    public function getTypeColor(): string
    {
        return match($this->type) {
            'milestone' => 'blue',
            'meeting' => 'green',
            'deadline' => 'red',
            'task' => 'yellow',
            default => 'gray'
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'completed' => 'green',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOverdue(): bool
    {
        return $this->eventDate < new \DateTime() && $this->status !== 'completed';
    }

    public function markAsCompleted(): self
    {
        $this->status = 'completed';
        $this->completedAt = new \DateTime();
        return $this;
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
            'title' => $this->getTitle(),
            'description' => $this->getDescription(),
            'type' => $this->getType(),
            'status' => $this->getStatus(),
            'event_date' => $this->getEventDate()->format('c'),
            'completed_at' => $this->getCompletedAt()?->format('c'),
            'project_id' => $this->getProject()->getId(),
            'created_by' => $this->getCreatedBy()?->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
