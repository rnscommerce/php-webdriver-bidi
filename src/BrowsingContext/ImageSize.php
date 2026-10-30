<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ImageSize
{
    public function __construct(
        public ?int $maxWidth = null,
        public ?int $maxHeight = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxWidth: isset($data['maxWidth']) ? Wire::int($data['maxWidth']) : null,
            maxHeight: isset($data['maxHeight']) ? Wire::int($data['maxHeight']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->maxWidth !== null) {
            $data['maxWidth'] = $this->maxWidth;
        }
        if ($this->maxHeight !== null) {
            $data['maxHeight'] = $this->maxHeight;
        }
        return $data;
    }
}
