<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class SetLocalValue implements LocalValue
{
    /**
     * @param list<LocalValue> $value
     */
    public function __construct(
        public readonly array $value,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: Wire::records($data['value'] ?? null, LocalValueFactory::fromArray(...)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'set',
            'value' => Wire::encode($this->value),
        ];
    }
}
