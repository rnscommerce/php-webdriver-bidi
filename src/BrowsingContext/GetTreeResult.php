<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class GetTreeResult
{
    /**
     * @param list<Info> $contexts
     */
    public function __construct(
        public readonly array $contexts,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            contexts: Wire::records($data['contexts'] ?? null, Info::fromArray(...)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'contexts' => Wire::encode($this->contexts),
        ];
    }
}
