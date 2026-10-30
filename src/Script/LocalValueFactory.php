<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

final class LocalValueFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): LocalValue
    {
        return match ($data['type'] ?? null) {
            'undefined' => UndefinedValue::fromArray($data),
            'null' => NullValue::fromArray($data),
            'string' => StringValue::fromArray($data),
            'number' => NumberValue::fromArray($data),
            'boolean' => BooleanValue::fromArray($data),
            'bigint' => BigIntValue::fromArray($data),
            'channel' => ChannelValue::fromArray($data),
            'array' => ArrayLocalValue::fromArray($data),
            'date' => DateLocalValue::fromArray($data),
            'map' => MapLocalValue::fromArray($data),
            'object' => ObjectLocalValue::fromArray($data),
            'regexp' => RegExpLocalValue::fromArray($data),
            'set' => SetLocalValue::fromArray($data),
            default => RemoteReferenceFactory::fromArray($data),
        };
    }
}
