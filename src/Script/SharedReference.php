<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class SharedReference implements RemoteReference
{
    public function __construct(
        public readonly string $sharedId,
        public readonly ?string $handle = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            sharedId: Wire::string($data['sharedId'] ?? null),
            handle: isset($data['handle']) ? Wire::string($data['handle']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'sharedId' => $this->sharedId,
        ];
        if ($this->handle !== null) {
            $data['handle'] = $this->handle;
        }
        return $data;
    }
}
