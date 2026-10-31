<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class BoxClipRectangle implements ClipRectangle
{
    public function __construct(
        public readonly int|float $x,
        public readonly int|float $y,
        public readonly int|float $width,
        public readonly int|float $height,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            x: Wire::number($data['x'] ?? null),
            y: Wire::number($data['y'] ?? null),
            width: Wire::number($data['width'] ?? null),
            height: Wire::number($data['height'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => 'box',
            'x' => $this->x,
            'y' => $this->y,
            'width' => $this->width,
            'height' => $this->height,
        ];
    }
}
