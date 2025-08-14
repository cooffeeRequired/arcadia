<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'module_controllers')]
class ModuleController implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'controllers')]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $namespace;

    #[ORM\Column(type: 'string', length: 500, nullable: false)]
    private string $extends;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $methods = null;

    #[ORM\Column(name: 'file_path', type: 'string', length: 500, nullable: false)]
    private string $filePath;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => true])]
    private bool $enabled = true;

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

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getExtends(): string
    {
        return $this->extends;
    }

    public function getMethods(): ?array
    {
        return $this->methods;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
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

    public function setMethods(?array $methods): self
    {
        $this->methods = $methods;
        return $this;
    }

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // Helper methods
    public function getMethodsCount(): int
    {
        return $this->methods ? count($this->methods) : 0;
    }

    public function hasMethod(string $methodName): bool
    {
        return $this->methods && in_array($methodName, $this->methods);
    }

    public function addMethod(string $methodName): self
    {
        if (!$this->methods) {
            $this->methods = [];
        }
        if (!in_array($methodName, $this->methods)) {
            $this->methods[] = $methodName;
        }
        return $this;
    }

    public function removeMethod(string $methodName): self
    {
        if ($this->methods) {
            $this->methods = array_filter($this->methods, fn($method) => $method !== $methodName);
        }
        return $this;
    }

    // JsonSerializable implementation
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'namespace' => $this->namespace,
            'extends' => $this->extends,
            'methods' => $this->methods,
            'file_path' => $this->filePath,
            'enabled' => $this->enabled,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'methods_count' => $this->getMethodsCount(),
            'lines_count' => file_exists($this->filePath) ? substr_count(file_get_contents($this->filePath), "\n") + 1 : 0
        ];
    }
}
