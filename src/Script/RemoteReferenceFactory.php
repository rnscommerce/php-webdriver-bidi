<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class RemoteReferenceFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): RemoteReference
    {
        return match (true) {
            isset($data['sharedId']) => SharedReference::fromArray($data),
            isset($data['handle']) => RemoteObjectReference::fromArray($data),
            default => throw Wire::unknown('RemoteReference', $data),
        };
    }
}
