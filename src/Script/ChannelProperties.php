<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ChannelProperties
{
    public function __construct(
        public string $channel,
        public ?SerializationOptions $serializationOptions = null,
        public ?ResultOwnership $ownership = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            channel: Wire::string($data['channel'] ?? null),
            serializationOptions: isset($data['serializationOptions'])
                ? SerializationOptions::fromArray(Wire::map($data['serializationOptions']))
                : null,
            ownership: isset($data['ownership']) ? ResultOwnership::from(Wire::string($data['ownership'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'channel' => $this->channel,
        ];
        if ($this->serializationOptions !== null) {
            $data['serializationOptions'] = $this->serializationOptions->toArray();
        }
        if ($this->ownership !== null) {
            $data['ownership'] = $this->ownership->value;
        }
        return $data;
    }
}
