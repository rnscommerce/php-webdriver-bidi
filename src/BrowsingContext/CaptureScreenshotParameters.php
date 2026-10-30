<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class CaptureScreenshotParameters
{
    public function __construct(
        public string $context,
        public ?CaptureScreenshotParametersOrigin $origin = null,
        public ?ImageFormat $format = null,
        public ?ClipRectangle $clip = null,
        public ?ImageSize $imageSize = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: Wire::string($data['context'] ?? null),
            origin: isset($data['origin'])
                ? CaptureScreenshotParametersOrigin::from(Wire::string($data['origin']))
                : null,
            format: isset($data['format']) ? ImageFormat::fromArray(Wire::map($data['format'])) : null,
            clip: isset($data['clip']) ? ClipRectangleFactory::fromArray(Wire::map($data['clip'])) : null,
            imageSize: isset($data['imageSize']) ? ImageSize::fromArray(Wire::map($data['imageSize'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'context' => $this->context,
        ];
        if ($this->origin !== null) {
            $data['origin'] = $this->origin->value;
        }
        if ($this->format !== null) {
            $data['format'] = $this->format->toArray();
        }
        if ($this->clip !== null) {
            $data['clip'] = $this->clip->toArray();
        }
        if ($this->imageSize !== null) {
            $data['imageSize'] = $this->imageSize->toArray();
        }
        return $data;
    }
}
