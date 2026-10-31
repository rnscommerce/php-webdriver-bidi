<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class CaptureScreenshotParameters
{
    public function __construct(
        public readonly string $context,
        public readonly ?CaptureScreenshotParametersOrigin $origin = null,
        public readonly ?ImageFormat $format = null,
        public readonly ?ClipRectangle $clip = null,
        public readonly ?ImageSize $imageSize = null,
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
            $data['format'] = Wire::encode($this->format);
        }
        if ($this->clip !== null) {
            $data['clip'] = Wire::encode($this->clip);
        }
        if ($this->imageSize !== null) {
            $data['imageSize'] = Wire::encode($this->imageSize);
        }
        return $data;
    }
}
