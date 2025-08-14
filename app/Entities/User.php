<?php /** @noinspection ALL */

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'users')]
class User implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    protected int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'name')]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, unique: true, name: 'email')]
    private string $email;

    #[ORM\Column(type: 'string', length: 255, name: 'password')]
    private string $password;

    #[ORM\Column(type: 'string', length: 50, name: 'role')]
    private string $role = 'user'; // admin, manager, user

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'avatar')]
    private ?string $avatar = null;

    #[ORM\Column(type: 'boolean', name: 'is_active')]
    private bool $is_active = true;

    #[ORM\Column(type: 'datetime', nullable: true, name: 'last_login')]
    private ?\DateTime $last_login = null;

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
    public function getEmail(): string { return $this->email; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string { return $this->role; }
    public function getAvatar(): ?string { return $this->avatar; }
    public function isActive(): bool { return $this->is_active; }
    public function getLastLogin(): ?\DateTime { return $this->last_login; }
    public function getCreatedAt(): \DateTime { return $this->created_at; }
    public function getUpdatedAt(): \DateTime { return $this->updated_at; }

    // Setters
    public function setName(string $name): self { $this->name = $name; return $this; }
    public function setEmail(string $email): self { $this->email = $email; return $this; }
    public function setPassword(string $password): self { $this->password = $password; return $this; }
    public function setRole(string $role): self { $this->role = $role; return $this; }
    public function setAvatar(?string $avatar): self { $this->avatar = $avatar; return $this; }
    public function setIsActive(bool $is_active): self { $this->is_active = $is_active; return $this; }
    public function setLastLogin(?\DateTime $last_login): self { $this->last_login = $last_login; return $this; }

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
            'email' => $this->getEmail(),
            'role' => $this->getRole(),
            'avatar' => $this->getAvatar(),
            'is_active' => $this->isActive(),
            'last_login' => $this->getLastLogin()?->format('c'),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }

    public function only(array $columns): array
    {
        return array_intersect_key(
            $this->jsonSerialize(),
            array_flip($columns)
        );
    }
}
