<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'module_translations')]
class ModuleTranslation implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'translations')]
    #[ORM\JoinColumn(name: 'module_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[ORM\Column(type: 'string', length: 10, nullable: false)]
    private string $locale;

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $key;

    #[ORM\Column(type: 'text', nullable: false)]
    private string $value;

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

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getValue(): string
    {
        return $this->value;
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

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function setKey(string $key): self
    {
        $this->key = $key;
        return $this;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;
        return $this;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    // JsonSerializable implementation
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'locale' => $this->locale,
            'key' => $this->key,
            'value' => $this->value,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s')
        ];
    }
}
