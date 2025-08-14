<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'module_entities')]
class ModuleEntity implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'entities')]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'table_name', type: 'string', length: 255, nullable: false)]
    private string $tableName;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $namespace;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $extends;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $properties = null;

    #[ORM\Column(name: 'file_path', type: 'string', length: 500, nullable: false)]
    private string $filePath;

    #[ORM\Column(name: 'table_exists', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $tableExists = false;

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

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getExtends(): string
    {
        return $this->extends;
    }

    public function getProperties(): ?array
    {
        return $this->properties;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function isTableExists(): bool
    {
        return $this->tableExists;
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

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;
        return $this;
    }

    public function setNamespace(string $namespace): self
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function setExtends(string $extends): self
    {
        $this->extends = $extends;
        return $this;
    }

    public function setProperties(?array $properties): self
    {
        $this->properties = $properties;
        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function setTableExists(bool $tableExists): self
    {
        $this->tableExists = $tableExists;
        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Helper methods
    public function getColumnsCount(): int
    {
        return $this->properties ? count($this->properties) : 0;
    }

    public function hasProperty(string $propertyName): bool
    {
        return $this->properties && in_array($propertyName, $this->properties);
    }

    public function addProperty(string $propertyName): self
    {
        if (!$this->properties) {
            $this->properties = [];
        }
        if (!in_array($propertyName, $this->properties)) {
            $this->properties[] = $propertyName;
        }
        return $this;
    }

    public function removeProperty(string $propertyName): self
    {
        if ($this->properties) {
            $this->properties = array_filter($this->properties, fn($property) => $property !== $propertyName);
        }
        return $this;
    }

    public function hasTimestamps(): bool
    {
        return $this->hasProperty('timestamps');
    }

    public function hasSoftDeletes(): bool
    {
        return $this->hasProperty('soft_deletes');
    }

    public function hasFillable(): bool
    {
        return $this->hasProperty('fillable');
    }

    // JsonSerializable implementation
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'table' => $this->tableName,
            'table_name' => $this->tableName,
            'namespace' => $this->namespace,
            'extends' => $this->extends,
            'properties' => $this->properties,
            'file_path' => $this->filePath,
            'table_exists' => $this->tableExists,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'columns_count' => $this->getColumnsCount(),
            'lines_count' => file_exists($this->filePath) ? substr_count(file_get_contents($this->filePath), "\n") + 1 : 0
        ];
    }
}
