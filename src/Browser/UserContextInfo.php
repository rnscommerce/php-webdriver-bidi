<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Browser;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class UserContextInfo
{
    public function __construct(
        public string $userContext,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            userContext: Wire::string($data['userContext'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'userContext' => $this->userContext,
        ];
    }
}
