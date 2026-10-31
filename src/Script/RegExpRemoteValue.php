<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class RegExpRemoteValue implements RemoteValue
{
    public function __construct(
        public readonly RegExpValue $value,
        public readonly ?string $handle = null,
        public readonly ?string $internalId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            value: RegExpValue::fromArray(Wire::map($data['value'] ?? null)),
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
            'type' => 'regexp',
            'value' => Wire::encode($this->value),
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
