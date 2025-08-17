<?php
namespace Core\Modules\DTO;

final readonly class EntityInfo
{
    public function __construct(
        public string $name,
        public string $tableName,
        public string $namespace,
        public string $extends,
        /** @var array<string,mixed> */
        public array $properties,
        public string $filePath,
        public bool $tableExists,
        public string $source,
        public int $columnsCount,
        public int $linesCount,
        public ?int $id = null,
        public ?string $createdAt = null,
    ) {}
}