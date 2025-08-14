<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'email_signatures')]
class EmailSignature implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'name')]
    private string $name;

    #[ORM\Column(type: 'text', name: 'content')]
    private string $content;

    #[ORM\Column(type: 'boolean', name: 'is_default')]
    private bool $is_default = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    private \DateTime $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getContent(): string { return $this->content; }
    public function getIsDefault(): bool { return $this->is_default; }
    public function getUser(): User { return $this->user; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function setIsDefault(bool $is_default): self { $this->is_default = $is_default; return $this; }
    public function setUser(User $user): self { $this->user = $user; return $this; }

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
            'content' => $this->getContent(),
            'is_default' => $this->getIsDefault(),
            'user_id' => $this->getUser()->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
