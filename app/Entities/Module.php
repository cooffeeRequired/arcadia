<?php

namespace App\Entities;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'modules')]
class Module implements JsonSerializable
{
    /** @noinspection PhpGetterAndSetterCanBeReplacedWithPropertyHooksInspection */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\Column(type: 'string', length: 100, unique: true)]
    protected string $name;

    #[ORM\Column(name: 'display_name', type: 'string', length: 255)]
    protected string $displayName;

    #[ORM\Column(type: 'text', nullable: true)]
    protected ?string $description = null;

    #[ORM\Column(type: 'string', length: 20)]
    protected string $version;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $author = null;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $icon = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected ?string $color = null;


    #[ORM\Column(name: 'sort_order', type: 'integer', nullable: true)]
    protected ?int $sortOrder = null;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $dependencies = null;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $settings = null;

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $permissions = null;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    protected DateTime $createdAt;

    #[ORM\Column(name: 'updated_at', type: 'datetime')]
    protected DateTime $updatedAt;

    #[ORM\Column(name: 'install_date', type: 'datetime', nullable: true)]
    protected ?DateTime $installDate = null;


    #[ORM\Column(name: 'is_enabled', type: 'boolean')]
    protected bool $isEnabled = false;

    #[ORM\Column(name: 'is_installed', type: 'boolean')]
    protected bool $isInstalled = false;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getVersion(): string
    {
        return $this->version;
    }

    public function getAuthor(): ?string
    {
        return $this->author;
    }

    public function isEnabled(): bool
    {
        return $this->isEnabled;
    }

    public function isInstalled(): bool
    {
        return $this->isInstalled;
    }

    public function getInstallDate(): ?DateTime
    {
        return $this->installDate;
    }

    public function getUpdateDate(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function getDependencies(): ?array
    {
        return $this->dependencies;
    }

    public function getSettings(): ?array
    {
        return $this->settings;
    }

    public function getPermissions(): ?array
    {
        return $this->permissions;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return $this->updatedAt;
    }

    // Setters
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setDisplayName(string $display_name): self
    {
        $this->displayName = $display_name;
        return $this;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;
        return $this;
    }

    public function setAuthor(?string $author): self
    {
        $this->author = $author;
        return $this;
    }

    public function setIsEnabled(bool $is_enabled): self
    {
        $this->isEnabled = $is_enabled;
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function setIsInstalled(bool $is_installed): self
    {
        $this->isInstalled = $is_installed;
        if ($is_installed && !$this->installDate) {
            $this->installDate = new DateTime();
        }
        $this->updatedAt = new DateTime();
        return $this;
    }

    public function setInstallDate(?DateTime $install_date): self
    {
        $this->installDate = $install_date;
        return $this;
    }

    public function setUpdateDate(?DateTime $update_date): self
    {
        $this->updatedAt = $update_date;
        return $this;
    }

    public function setDependencies(?array $dependencies): self
    {
        $this->dependencies = $dependencies;
        return $this;
    }

    public function setSettings(?array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }

    public function setPermissions(?array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function setCreatedAt(DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setUpdatedAt(DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    /**
     * Implementace JsonSerializable
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'display_name' => $this->displayName,
            'description' => $this->description,
            'version' => $this->version,
            'author' => $this->author,
            'is_enabled' => $this->isEnabled,
            'is_installed' => $this->isInstalled,
            'install_date' => $this->installDate?->format('Y-m-d H:i:s'),
            'update_date' => $this->updatedAt->format('Y-m-d H:i:s'),
            'dependencies' => $this->dependencies,
            'settings' => $this->settings,
            'permissions' => $this->permissions,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
            'sort_order' => $this->sortOrder,
        ];
    }
}
