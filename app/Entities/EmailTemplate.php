<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'email_templates')]
class EmailTemplate implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'name')]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, name: 'subject')]
    private string $subject;

    #[ORM\Column(type: 'text', name: 'content')]
    private string $content;

    #[ORM\Column(type: 'string', length: 50, name: 'category')]
    private string $category; // welcome, follow_up, invoice, reminder, etc.

    #[ORM\Column(type: 'boolean', name: 'is_active')]
    private bool $is_active = true;

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
    public function getSubject(): string { return $this->subject; }
    public function getContent(): string { return $this->content; }
    public function getCategory(): string { return $this->category; }
    public function getIsActive(): bool { return $this->is_active; }
    public function getUser(): User { return $this->user; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setSubject(string $subject): self { $this->subject = $subject; return $this; }
    public function setContent(string $content): self { $this->content = $content; return $this; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }
    public function setIsActive(bool $is_active): self { $this->is_active = $is_active; return $this; }
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
            'subject' => $this->getSubject(),
            'content' => $this->getContent(),
            'category' => $this->getCategory(),
            'is_active' => $this->getIsActive(),
            'user_id' => $this->getUser()->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
