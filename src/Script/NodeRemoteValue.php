<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class NodeRemoteValue implements RemoteValue
{
    public function __construct(
        public readonly ?string $sharedId = null,
        public readonly ?string $handle = null,
        public readonly ?string $internalId = null,
        public readonly ?NodeProperties $value = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sharedId: isset($data['sharedId']) ? Wire::string($data['sharedId']) : null,
            handle: isset($data['handle']) ? Wire::string($data['handle']) : null,
            internalId: isset($data['internalId']) ? Wire::string($data['internalId']) : null,
            value: isset($data['value']) ? NodeProperties::fromArray(Wire::map($data['value'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'node',
        ];
        if ($this->sharedId !== null) {
            $data['sharedId'] = $this->sharedId;
        }
        if ($this->handle !== null) {
            $data['handle'] = $this->handle;
        }
        if ($this->internalId !== null) {
            $data['internalId'] = $this->internalId;
        }
        if ($this->value !== null) {
            $data['value'] = Wire::encode($this->value);
        }
        return $data;
    }
}
