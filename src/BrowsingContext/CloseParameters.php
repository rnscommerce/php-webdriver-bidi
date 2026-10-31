<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class CloseParameters
{
    public function __construct(
        public readonly string $context,
        public readonly ?bool $promptUnload = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: Wire::string($data['context'] ?? null),
            promptUnload: isset($data['promptUnload']) ? Wire::bool($data['promptUnload']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'context' => $this->context,
        ];
        if ($this->promptUnload !== null) {
            $data['promptUnload'] = $this->promptUnload;
        }
        return $data;
    }
}
