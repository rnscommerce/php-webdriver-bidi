<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class ExceptionDetails
{
    public function __construct(
        public readonly int $columnNumber,
        public readonly RemoteValue $exception,
        public readonly int $lineNumber,
        public readonly StackTrace $stackTrace,
        public readonly string $text,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            columnNumber: Wire::int($data['columnNumber'] ?? null),
            exception: RemoteValueFactory::fromArray(Wire::map($data['exception'] ?? null)),
            lineNumber: Wire::int($data['lineNumber'] ?? null),
            stackTrace: StackTrace::fromArray(Wire::map($data['stackTrace'] ?? null)),
            text: Wire::string($data['text'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'columnNumber' => $this->columnNumber,
            'exception' => Wire::encode($this->exception),
            'lineNumber' => $this->lineNumber,
            'stackTrace' => Wire::encode($this->stackTrace),
            'text' => $this->text,
        ];
    }
}
