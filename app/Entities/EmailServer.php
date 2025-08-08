<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'email_servers')]
class EmailServer implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $host;

    #[ORM\Column(type: 'integer')]
    private int $port;

    #[ORM\Column(type: 'string', length: 50)]
    private string $encryption; // ssl, tls, none

    #[ORM\Column(type: 'string', length: 255)]
    private string $username;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'string', length: 255)]
    private string $from_email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $from_name;

    #[ORM\Column(type: 'boolean')]
    private bool $is_active = true;

    #[ORM\Column(type: 'boolean')]
    private bool $is_default = false;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id')]
    private User $user;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $created_at;

    #[ORM\Column(type: 'datetime')]
    private \DateTime $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getHost(): string { return $this->host; }
    public function getPort(): int { return $this->port; }
    public function getEncryption(): string { return $this->encryption; }
    public function getUsername(): string { return $this->username; }
    public function getPassword(): string { return $this->password; }
    public function getFromEmail(): string { return $this->from_email; }
    public function getFromName(): string { return $this->from_name; }
    public function getIsActive(): bool { return $this->is_active; }
    public function getIsDefault(): bool { return $this->is_default; }
    public function getUser(): User { return $this->user; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setHost(string $host): self { $this->host = $host; return $this; }
    public function setPort(int $port): self { $this->port = $port; return $this; }
    public function setEncryption(string $encryption): self { $this->encryption = $encryption; return $this; }
    public function setUsername(string $username): self { $this->username = $username; return $this; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function setFromEmail(string $from_email): self { $this->from_email = $from_email; return $this; }
    public function setFromName(string $from_name): self { $this->from_name = $from_name; return $this; }
    public function setIsActive(bool $is_active): self { $this->is_active = $is_active; return $this; }
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
            'host' => $this->getHost(),
            'port' => $this->getPort(),
            'encryption' => $this->getEncryption(),
            'username' => $this->getUsername(),
            'from_email' => $this->getFromEmail(),
            'from_name' => $this->getFromName(),
            'is_active' => $this->getIsActive(),
            'is_default' => $this->getIsDefault(),
            'user_id' => $this->getUser()->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
