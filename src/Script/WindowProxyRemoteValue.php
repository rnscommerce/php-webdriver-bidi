<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class WindowProxyRemoteValue implements RemoteValue
{
    public function __construct(
        public WindowProxyProperties $value,
        public ?string $handle = null,
        public ?string $internalId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: WindowProxyProperties::fromArray(Wire::map($data['value'] ?? null)),
            handle: isset($data['handle']) ? Wire::string($data['handle']) : null,
            internalId: isset($data['internalId']) ? Wire::string($data['internalId']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'window',
            'value' => $this->value->toArray(),
        ];
        if ($this->handle !== null) {
            $data['handle'] = $this->handle;
        }
        if ($this->internalId !== null) {
            $data['internalId'] = $this->internalId;
        }
        return $data;
    }
}
