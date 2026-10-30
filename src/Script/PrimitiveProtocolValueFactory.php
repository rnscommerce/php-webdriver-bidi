<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class PrimitiveProtocolValueFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): PrimitiveProtocolValue
    {
        return match ($data['type'] ?? null) {
            'undefined' => UndefinedValue::fromArray($data),
            'null' => NullValue::fromArray($data),
            'string' => StringValue::fromArray($data),
            'number' => NumberValue::fromArray($data),
            'boolean' => BooleanValue::fromArray($data),
            'bigint' => BigIntValue::fromArray($data),
            default => throw Wire::unknown('PrimitiveProtocolValue', $data),
        };
    }
}
