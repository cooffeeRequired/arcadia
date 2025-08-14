<?php

namespace App\Entities;

use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'project_files')]
class ProjectFile implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', name: 'id')]
    private int $id;

    #[ORM\Column(type: 'string', length: 255, name: 'filename')]
    private string $filename;

    #[ORM\Column(type: 'string', length: 255, name: 'original_filename')]
    private string $originalFilename;

    #[ORM\Column(type: 'string', length: 100, name: 'mime_type')]
    private string $mimeType;

    #[ORM\Column(type: 'integer', name: 'file_size')]
    private int $fileSize;

    #[ORM\Column(type: 'string', length: 255, name: 'file_path')]
    private string $filePath;

    #[ORM\Column(type: 'string', length: 255, nullable: true, name: 'description')]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 50, name: 'category')]
    private string $category = 'document';

    #[ORM\Column(type: 'datetime', name: 'created_at')]
    private \DateTime $createdAt;

    #[ORM\Column(type: 'datetime', name: 'updated_at')]
    private \DateTime $updatedAt;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'files')]
    #[ORM\JoinColumn(name: 'project_id', referencedColumnName: 'id', nullable: false)]
    private Project $project;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'uploaded_by', referencedColumnName: 'id', nullable: true)]
    private ?User $uploadedBy = null;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    // Getters
    public function getId(): int { return $this->id; }
    public function getFilename(): string { return $this->filename; }
    public function getOriginalFilename(): string { return $this->originalFilename; }
    public function getMimeType(): string { return $this->mimeType; }
    public function getFileSize(): int { return $this->fileSize; }
    public function getFilePath(): string { return $this->filePath; }
    public function getDescription(): ?string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }
    public function getUpdatedAt(): \DateTime { return $this->updatedAt; }
    public function getProject(): Project { return $this->project; }
    public function getUploadedBy(): ?User { return $this->uploadedBy; }

    // Setters
    public function setFilename(string $filename): self { $this->filename = $filename; return $this; }
    public function setOriginalFilename(string $originalFilename): self { $this->originalFilename = $originalFilename; return $this; }
    public function setMimeType(string $mimeType): self { $this->mimeType = $mimeType; return $this; }
    public function setFileSize(int $fileSize): self { $this->fileSize = $fileSize; return $this; }
    public function setFilePath(string $filePath): self { $this->filePath = $filePath; return $this; }
    public function setDescription(?string $description): self { $this->description = $description; return $this; }
    public function setCategory(string $category): self { $this->category = $category; return $this; }
    public function setProject(Project $project): self { $this->project = $project; return $this; }
    public function setUploadedBy(?User $uploadedBy): self { $this->uploadedBy = $uploadedBy; return $this; }

    // Helper methods
    public function getFormattedFileSize(): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $size = $this->fileSize;
        $unit = 0;

        while ($size >= 1024 && $unit < count($units) - 1) {
            $size /= 1024;
            $unit++;
        }

        return round($size, 2) . ' ' . $units[$unit];
    }

    public function getFileExtension(): string
    {
        return pathinfo($this->originalFilename, PATHINFO_EXTENSION);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function isDocument(): bool
    {
        $documentTypes = [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        return in_array($this->mimeType, $documentTypes);
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTime();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'filename' => $this->getFilename(),
            'original_filename' => $this->getOriginalFilename(),
            'mime_type' => $this->getMimeType(),
            'file_size' => $this->getFileSize(),
            'formatted_file_size' => $this->getFormattedFileSize(),
            'file_path' => $this->getFilePath(),
            'description' => $this->getDescription(),
            'category' => $this->getCategory(),
            'file_extension' => $this->getFileExtension(),
            'is_image' => $this->isImage(),
            'is_document' => $this->isDocument(),
            'project_id' => $this->getProject()->getId(),
            'uploaded_by' => $this->getUploadedBy()?->getId(),
            'created_at' => $this->getCreatedAt()->format('c'),
            'updated_at' => $this->getUpdatedAt()->format('c')
        ];
    }
}
