<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class NumberValue implements PrimitiveProtocolValue
{
    public function __construct(
        public int|float|SpecialNumber $value,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: \is_int($data['value'] ?? null) || \is_float($data['value'] ?? null)
                ? Wire::number($data['value'])
                : SpecialNumber::from(Wire::string($data['value'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'number',
            'value' => Wire::encode($this->value),
        ];
    }
}
