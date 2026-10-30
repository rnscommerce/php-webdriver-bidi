<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class GetDataResult
{
    public function __construct(
        public BytesValue $bytes,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            bytes: BytesValueFactory::fromArray(Wire::map($data['bytes'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'bytes' => Wire::encode($this->bytes),
        ];
    }
}
