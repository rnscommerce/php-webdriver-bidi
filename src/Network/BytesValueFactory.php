<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final class BytesValueFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): BytesValue
    {
        return match ($data['type'] ?? null) {
            'string' => StringValue::fromArray($data),
            'base64' => Base64Value::fromArray($data),
            default => throw Wire::unknown('BytesValue', $data),
        };
    }
}
