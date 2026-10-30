<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class EvaluationResultException implements EvaluationResult
{
    public function __construct(
        public ExceptionDetails $exceptionDetails,
        public string $realm,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            exceptionDetails: ExceptionDetails::fromArray(Wire::map($data['exceptionDetails'] ?? null)),
            realm: Wire::string($data['realm'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'exception',
            'exceptionDetails' => $this->exceptionDetails->toArray(),
            'realm' => $this->realm,
        ];
    }
}
