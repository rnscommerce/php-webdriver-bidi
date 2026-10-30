<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class StartScreencastParameters
{
    public function __construct(
        public string $context,
        public ?string $destinationFolder = null,
        public ?string $mimeType = null,
        public ?MediaTrackConstraints $video = null,
        public ?bool $audio = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: Wire::string($data['context'] ?? null),
            destinationFolder: isset($data['destinationFolder']) ? Wire::string($data['destinationFolder']) : null,
            mimeType: isset($data['mimeType']) ? Wire::string($data['mimeType']) : null,
            video: isset($data['video']) ? MediaTrackConstraints::fromArray(Wire::map($data['video'])) : null,
            audio: isset($data['audio']) ? Wire::bool($data['audio']) : null,
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
        if ($this->destinationFolder !== null) {
            $data['destinationFolder'] = $this->destinationFolder;
        }
        if ($this->mimeType !== null) {
            $data['mimeType'] = $this->mimeType;
        }
        if ($this->video !== null) {
            $data['video'] = $this->video->toArray();
        }
        if ($this->audio !== null) {
            $data['audio'] = $this->audio;
        }
        return $data;
    }
}
