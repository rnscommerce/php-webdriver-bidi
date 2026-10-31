<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class SerializationOptions
{
    public function __construct(
        public readonly ?int $maxDomDepth = null,
        public readonly ?int $maxObjectDepth = null,
        public readonly ?SerializationOptionsIncludeShadowTree $includeShadowTree = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            maxDomDepth: isset($data['maxDomDepth']) ? Wire::int($data['maxDomDepth']) : null,
            maxObjectDepth: isset($data['maxObjectDepth']) ? Wire::int($data['maxObjectDepth']) : null,
            includeShadowTree: isset($data['includeShadowTree'])
                ? SerializationOptionsIncludeShadowTree::from(Wire::string($data['includeShadowTree']))
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->maxDomDepth !== null) {
            $data['maxDomDepth'] = $this->maxDomDepth;
        }
        if ($this->maxObjectDepth !== null) {
            $data['maxObjectDepth'] = $this->maxObjectDepth;
        }
        if ($this->includeShadowTree !== null) {
            $data['includeShadowTree'] = $this->includeShadowTree->value;
        }
        return $data;
    }
}
