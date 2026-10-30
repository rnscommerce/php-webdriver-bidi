<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Script\StackTrace;
use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class Initiator
{
    public function __construct(
        public ?int $columnNumber = null,
        public ?int $lineNumber = null,
        public ?string $request = null,
        public ?StackTrace $stackTrace = null,
        public ?InitiatorType $type = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            columnNumber: isset($data['columnNumber']) ? Wire::int($data['columnNumber']) : null,
            lineNumber: isset($data['lineNumber']) ? Wire::int($data['lineNumber']) : null,
            request: isset($data['request']) ? Wire::string($data['request']) : null,
            stackTrace: isset($data['stackTrace']) ? StackTrace::fromArray(Wire::map($data['stackTrace'])) : null,
            type: isset($data['type']) ? InitiatorType::from(Wire::string($data['type'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->columnNumber !== null) {
            $data['columnNumber'] = $this->columnNumber;
        }
        if ($this->lineNumber !== null) {
            $data['lineNumber'] = $this->lineNumber;
        }
        if ($this->request !== null) {
            $data['request'] = $this->request;
        }
        if ($this->stackTrace !== null) {
            $data['stackTrace'] = Wire::encode($this->stackTrace);
        }
        if ($this->type !== null) {
            $data['type'] = $this->type->value;
        }
        return $data;
    }
}
