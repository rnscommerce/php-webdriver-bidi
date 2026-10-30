<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class WindowProxyProperties
{
    public function __construct(
        public string $context,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: Wire::string($data['context'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'context' => $this->context,
        ];
    }
}
