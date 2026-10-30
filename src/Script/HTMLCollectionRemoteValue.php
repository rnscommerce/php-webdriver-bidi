<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class HTMLCollectionRemoteValue implements RemoteValue
{
    /**
     * @param ?list<RemoteValue> $value
     */
    public function __construct(
        public ?string $handle = null,
        public ?string $internalId = null,
        public ?array $value = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: isset($data['handle']) ? Wire::string($data['handle']) : null,
            internalId: isset($data['internalId']) ? Wire::string($data['internalId']) : null,
            value: isset($data['value']) ? Wire::records($data['value'], RemoteValueFactory::fromArray(...)) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'htmlcollection',
        ];
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
