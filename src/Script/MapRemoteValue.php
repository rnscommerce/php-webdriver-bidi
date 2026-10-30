<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class MapRemoteValue implements RemoteValue
{
    /**
     * @param list<list<RemoteValue|string>>|null $value
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
            value: isset($data['value'])
                ? Wire::list(
                    $data['value'],
                    static fn (mixed $item): array => Wire::list(
                        $item,
                        static fn (mixed $item): RemoteValue|string => \is_array($item)
                            ? RemoteValueFactory::fromArray(Wire::map($item))
                            : Wire::string($item),
                    ),
                )
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => 'map',
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
