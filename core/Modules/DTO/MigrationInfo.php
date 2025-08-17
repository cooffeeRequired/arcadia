<?php
namespace Core\Modules\DTO;

final readonly class MigrationInfo
{
    public function __construct(
        public string $name,
        public string $file,
        public string $type,            // create_table|…
        public ?string $tableName,
        public string $phpContent,
        public string $status,          // pending|ran|failed
        public ?string $ranAt,
        public ?string $errorMessage,
        public string $filePath,
        public string $source,          // config|database|merged
        public int $linesCount,
        public ?int $id = null,
        public ?string $createdAt = null,
    ) {}
}
