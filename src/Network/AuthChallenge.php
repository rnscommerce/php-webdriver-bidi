<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final class AuthChallenge
{
    public function __construct(
        public readonly string $scheme,
        public readonly string $realm,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scheme: Wire::string($data['scheme'] ?? null),
            realm: Wire::string($data['realm'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'scheme' => $this->scheme,
            'realm' => $this->realm,
        ];
    }
}
