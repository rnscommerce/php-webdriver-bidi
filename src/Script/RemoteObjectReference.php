<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class RemoteObjectReference implements RemoteReference
{
    public function __construct(
        public string $handle,
        public ?string $sharedId = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            handle: Wire::string($data['handle'] ?? null),
            sharedId: isset($data['sharedId']) ? Wire::string($data['sharedId']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'handle' => $this->handle,
        ];
        if ($this->sharedId !== null) {
            $data['sharedId'] = $this->sharedId;
        }
        return $data;
    }
}
