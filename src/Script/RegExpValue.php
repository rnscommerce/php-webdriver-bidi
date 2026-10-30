<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class RegExpValue
{
    public function __construct(
        public string $pattern,
        public ?string $flags = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            pattern: Wire::string($data['pattern'] ?? null),
            flags: isset($data['flags']) ? Wire::string($data['flags']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'pattern' => $this->pattern,
        ];
        if ($this->flags !== null) {
            $data['flags'] = $this->flags;
        }
        return $data;
    }
}
