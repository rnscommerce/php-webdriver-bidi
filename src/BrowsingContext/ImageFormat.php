<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ImageFormat
{
    public function __construct(
        public string $type,
        public int|float|null $quality = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: Wire::string($data['type'] ?? null),
            quality: isset($data['quality']) ? Wire::number($data['quality']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type,
        ];
        if ($this->quality !== null) {
            $data['quality'] = $this->quality;
        }
        return $data;
    }
}
