<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'module_views')]
class ModuleView implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'views')]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'file_path', type: 'string', length: 500, nullable: false)]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 50, nullable: false)]
    private string $type;

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

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getType(): string
    {
        return $this->type;
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

    public function setFilePath(string $filePath): self
    {
        $this->filePath = $filePath;
        return $this;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
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
    public function getFileSize(): int
    {
        return file_exists($this->filePath) ? filesize($this->filePath) : 0;
    }

    public function getLinesCount(): int
    {
        return file_exists($this->filePath) ? substr_count(file_get_contents($this->filePath), "\n") + 1 : 0;
    }

    // JsonSerializable implementation
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file_path' => $this->filePath,
            'type' => $this->type,
            'enabled' => $this->enabled,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'file_size' => $this->getFileSize(),
            'lines_count' => $this->getLinesCount()
        ];
    }
}
