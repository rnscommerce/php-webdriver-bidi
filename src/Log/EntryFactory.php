<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Log;

final class EntryFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): Entry
    {
        return match ($data['type'] ?? null) {
            'console' => ConsoleLogEntry::fromArray($data),
            'javascript' => JavascriptLogEntry::fromArray($data),
            default => GenericLogEntry::fromArray($data),
        };
    }
}
