<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'workflow_executions')]
class WorkflowExecution implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Workflow::class)]
    #[ORM\JoinColumn(name: 'workflow_id', referencedColumnName: 'id')]
    private Workflow $workflow;

    #[ORM\Column(type: 'string', length: 50, name: 'status')]
    private string $status; // running, completed, failed, cancelled

    #[ORM\Column(type: 'json', name: 'trigger_data')]
    private array $trigger_data = [];

    #[ORM\Column(type: 'json', name: 'execution_log')]
    private array $execution_log = [];

    #[ORM\Column(type: 'text', nullable: true, name: 'error_message')]
    private ?string $error_message = null;

    #[ORM\Column(type: 'datetime', name: 'started_at')]
    private \DateTime $started_at;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'completed_at')]
    private ?\DateTime $completed_at = null;

    #[ORM\Column(type: 'integer', name: 'execution_time_ms')]
    private int $execution_time_ms = 0;

    public function __construct()
    {
        $this->started_at = new \DateTime();
        $this->trigger_data = [];
        $this->execution_log = [];
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getWorkflow(): Workflow { return $this->workflow; }
    public function getStatus(): string { return $this->status; }
    public function getTriggerData(): array { return $this->trigger_data; }
    public function getExecutionLog(): array { return $this->execution_log; }
    public function getErrorMessage(): ?string { return $this->error_message; }
    public function getStartedAt(): \DateTime { return $this->started_at; }
    public function getCompletedAt(): ?\DateTime { return $this->completed_at; }
    public function getExecutionTimeMs(): int { return $this->execution_time_ms; }

    // Setters
    public function setWorkflow(Workflow $workflow): self { $this->workflow = $workflow; return $this; }
    public function setStatus(string $status): self { $this->status = $status; return $this; }
    public function setTriggerData(array $trigger_data): self { $this->trigger_data = $trigger_data; return $this; }
    public function setExecutionLog(array $execution_log): self { $this->execution_log = $execution_log; return $this; }
    public function setErrorMessage(?string $error_message): self { $this->error_message = $error_message; return $this; }
    public function setCompletedAt(?\DateTime $completed_at): self { $this->completed_at = $completed_at; return $this; }
    public function setExecutionTimeMs(int $execution_time_ms): self { $this->execution_time_ms = $execution_time_ms; return $this; }

    public function addExecutionLog(string $action, string $status, ?string $message = null): self
    {
        $this->execution_log[] = [
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'timestamp' => (new \DateTime())->format('c')
        ];
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'workflow_id' => $this->getWorkflow()->getId(),
            'status' => $this->getStatus(),
            'trigger_data' => $this->getTriggerData(),
            'execution_log' => $this->getExecutionLog(),
            'error_message' => $this->getErrorMessage(),
            'started_at' => $this->getStartedAt()->format('c'),
            'completed_at' => $this->getCompletedAt()?->format('c'),
            'execution_time_ms' => $this->getExecutionTimeMs()
        ];
    }
}
