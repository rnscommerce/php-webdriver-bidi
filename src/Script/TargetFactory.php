<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class TargetFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): Target
    {
        return match (true) {
            isset($data['context']) => ContextTarget::fromArray($data),
            isset($data['realm']) => RealmTarget::fromArray($data),
            default => throw Wire::unknown('Target', $data),
        };
    }
}
