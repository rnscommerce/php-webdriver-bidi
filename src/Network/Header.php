<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final class Header
{
    public function __construct(
        public readonly string $name,
        public readonly BytesValue $value,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Wire::string($data['name'] ?? null),
            value: BytesValueFactory::fromArray(Wire::map($data['value'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => Wire::encode($this->value),
        ];
    }
}
