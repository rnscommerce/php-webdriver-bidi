<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Script\SharedReference;
use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ElementClipRectangle implements ClipRectangle
{
    public function __construct(
        public SharedReference $element,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            element: SharedReference::fromArray(Wire::map($data['element'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'element',
            'element' => Wire::encode($this->element),
        ];
    }
}
