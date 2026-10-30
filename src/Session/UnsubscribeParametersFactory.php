<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final class UnsubscribeParametersFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): UnsubscribeParameters
    {
        return match (true) {
            isset($data['events']) => UnsubscribeByAttributesRequest::fromArray($data),
            isset($data['subscriptions']) => UnsubscribeByIDRequest::fromArray($data),
            default => throw Wire::unknown('UnsubscribeParameters', $data),
        };
    }
}
