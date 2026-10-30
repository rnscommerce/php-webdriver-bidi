<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class AddDataCollectorResult
{
    public function __construct(
        public string $collector,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            collector: Wire::string($data['collector'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'collector' => $this->collector,
        ];
    }
}
