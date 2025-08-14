<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'module_migrations')]
class ModuleMigration implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'migrations')]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $file;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $type;

    #[ORM\Column(name: 'table_name', type: 'string', length: 255, nullable: true)]
    private ?string $tableName = null;

    #[ORM\Column(name: 'sql_content', type: 'text', nullable: true)]
    private ?string $sqlContent = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false, options: ['default' => 'pending'])]
    private string $status = 'pending';

    #[ORM\Column(name: 'ran_at', type: 'datetime', nullable: true)]
    private ?\DateTime $ranAt = null;

    #[ORM\Column(name: 'error_message', type: 'text', nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(name: 'created_at', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private \DateTime $updatedAt;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    public function getSqlContent(): ?string
    {
        return $this->sqlContent;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getRanAt(): ?\DateTime
    {
        return $this->ranAt;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setModule(Module $module): self
    {
        $this->module = $module;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function setTableName(?string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function setSqlContent(?string $sqlContent): self
    {
        $this->sqlContent = $sqlContent;
        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function setRanAt(?\DateTime $ranAt): self
    {
        $this->ranAt = $ranAt;
        return $this;
    }

    public function setErrorMessage(?string $errorMessage): self
    {
        $this->errorMessage = $errorMessage;
        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isRan(): bool
    {
        return $this->status === 'ran';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isRolledBack(): bool
    {
        return $this->status === 'rolled_back';
    }

    public function markAsRan(): self
    {
        $this->status = 'ran';
        $this->ranAt = new \DateTime();
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function markAsFailed(string $errorMessage): self
    {
        $this->status = 'failed';
        $this->errorMessage = $errorMessage;
        $this->updatedAt = new \DateTime();
        return $this;
    }

    public function markAsRolledBack(): self
    {
        $this->status = 'rolled_back';
        $this->updatedAt = new \DateTime();
        return $this;
    }

    // JsonSerializable implementation
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file' => $this->file,
            'type' => $this->type,
            'table_name' => $this->tableName,
            'sql_content' => $this->sqlContent,
            'status' => $this->status,
            'ran_at' => $this->ranAt ? $this->ranAt->format('Y-m-d H:i:s') : null,
            'error_message' => $this->errorMessage,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'lines_count' => $this->sqlContent ? substr_count($this->sqlContent, "\n") + 1 : 0,
            'error' => $this->errorMessage
        ];
    }
}
