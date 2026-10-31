<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class StackFrame
{
    public function __construct(
        public readonly int $columnNumber,
        public readonly string $functionName,
        public readonly int $lineNumber,
        public readonly string $url,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            columnNumber: Wire::int($data['columnNumber'] ?? null),
            functionName: Wire::string($data['functionName'] ?? null),
            lineNumber: Wire::int($data['lineNumber'] ?? null),
            url: Wire::string($data['url'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'columnNumber' => $this->columnNumber,
            'functionName' => $this->functionName,
            'lineNumber' => $this->lineNumber,
            'url' => $this->url,
        ];
    }
}
