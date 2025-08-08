<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'workflows')]
class Workflow implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $trigger_type; // customer_created, deal_stage_changed, contact_added, etc.

    #[ORM\Column(type: 'json')]
    private array $trigger_config = [];

    #[ORM\Column(type: 'json')]
    private array $conditions = [];

    #[ORM\Column(type: 'json')]
    private array $actions = [];

    #[ORM\Column(type: 'boolean')]
    private bool $is_active = true;

    #[ORM\Column(type: 'integer')]
    private int $priority = 0;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updated_at;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id')]
    private User $created_by;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->trigger_config = [];
        $this->conditions = [];
        $this->actions = [];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getTriggerType(): string { return $this->trigger_type; }
    public function getTriggerConfig(): array { return $this->trigger_config; }
    public function getConditions(): array { return $this->conditions; }
    public function getActions(): array { return $this->actions; }
    public function isActive(): bool { return $this->is_active; }
    public function getPriority(): int { return $this->priority; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }
    public function getCreatedBy(): User { return $this->created_by; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setTriggerType(string $trigger_type): self { $this->trigger_type = $trigger_type; return $this; }
    public function setTriggerConfig(array $trigger_config): self { $this->trigger_config = $trigger_config; return $this; }
    public function setConditions(array $conditions): self { $this->conditions = $conditions; return $this; }
    public function setActions(array $actions): self { $this->actions = $actions; return $this; }
    public function setIsActive(bool $is_active): self { $this->is_active = $is_active; return $this; }
    public function setPriority(int $priority): self { $this->priority = $priority; return $this; }
    public function setCreatedBy(User $created_by): self { $this->created_by = $created_by; return $this; }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'name' => $this->getName(),
            'description' => $this->getDescription(),
            'trigger_type' => $this->getTriggerType(),
            'trigger_config' => $this->getTriggerConfig(),
            'conditions' => $this->getConditions(),
            'actions' => $this->getActions(),
            'is_active' => $this->isActive(),
            'priority' => $this->getPriority(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c'),
            'created_by' => $this->getCreatedBy()->getId()
        ];
    }
}
