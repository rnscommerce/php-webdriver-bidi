<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class GetTreeParameters
{
    public function __construct(
        public readonly ?int $maxDepth = null,
        public readonly ?string $root = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxDepth: isset($data['maxDepth']) ? Wire::int($data['maxDepth']) : null,
            root: isset($data['root']) ? Wire::string($data['root']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->maxDepth !== null) {
            $data['maxDepth'] = $this->maxDepth;
        }
        if ($this->root !== null) {
            $data['root'] = $this->root;
        }
        return $data;
    }
}
