<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class MediaTrackConstraints
{
    public function __construct(
        public ?int $width = null,
        public ?int $height = null,
        public ?int $frameRate = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            width: isset($data['width']) ? Wire::int($data['width']) : null,
            height: isset($data['height']) ? Wire::int($data['height']) : null,
            frameRate: isset($data['frameRate']) ? Wire::int($data['frameRate']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->width !== null) {
            $data['width'] = $this->width;
        }
        if ($this->height !== null) {
            $data['height'] = $this->height;
        }
        if ($this->frameRate !== null) {
            $data['frameRate'] = $this->frameRate;
        }
        return $data;
    }
}
