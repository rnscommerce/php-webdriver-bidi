<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class MessageParameters
{
    public function __construct(
        public string $channel,
        public RemoteValue $data,
        public Source $source,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            channel: Wire::string($data['channel'] ?? null),
            data: RemoteValueFactory::fromArray(Wire::map($data['data'] ?? null)),
            source: Source::fromArray(Wire::map($data['source'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'channel' => $this->channel,
            'data' => $this->data->toArray(),
            'source' => $this->source->toArray(),
        ];
    }
}
