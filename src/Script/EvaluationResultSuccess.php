<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class EvaluationResultSuccess implements EvaluationResult
{
    public function __construct(
        public RemoteValue $result,
        public string $realm,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            result: RemoteValueFactory::fromArray(Wire::map($data['result'] ?? null)),
            realm: Wire::string($data['realm'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'success',
            'result' => $this->result->toArray(),
            'realm' => $this->realm,
        ];
    }
}
