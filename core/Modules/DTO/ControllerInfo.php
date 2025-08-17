<?php
namespace Core\Modules\DTO;

final readonly class ControllerInfo
{
    public function __construct(
        public string $name,
        public string $namespace,
        public string $extends,
        /** @var array<string,string> method => signature */
        public array $methods,
        public string $filePath,
        public bool $enabled,
        public string $source,
        public int $methodsCount,
        public int $linesCount,
        public ?int $id = null,
        public ?string $createdAt = null,
    ) {}
}