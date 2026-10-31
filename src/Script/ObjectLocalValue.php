<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class ObjectLocalValue implements LocalValue
{
    /**
     * @param list<list<LocalValue|string>> $value
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
            value: Wire::list(
                $data['value'] ?? null,
                static fn (mixed $item): array => Wire::list(
                    $item,
                    static fn (mixed $item): LocalValue|string => \is_array($item)
                        ? LocalValueFactory::fromArray(Wire::map($item))
                        : Wire::string($item),
                ),
            ),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'object',
            'value' => Wire::encode($this->value),
        ];
    }
}
