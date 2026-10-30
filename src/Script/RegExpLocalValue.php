<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class RegExpLocalValue implements LocalValue
{
    public function __construct(
        public RegExpValue $value,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: RegExpValue::fromArray(Wire::map($data['value'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'regexp',
            'value' => $this->value->toArray(),
        ];
    }
}
