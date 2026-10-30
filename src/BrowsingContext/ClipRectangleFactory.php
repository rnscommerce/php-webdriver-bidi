<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class ClipRectangleFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ClipRectangle
    {
        return match ($data['type'] ?? null) {
            'box' => BoxClipRectangle::fromArray($data),
            'element' => ElementClipRectangle::fromArray($data),
            default => throw Wire::unknown('ClipRectangle', $data),
        };
    }
}
