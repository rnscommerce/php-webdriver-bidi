<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ContextTarget implements Target
{
    public function __construct(
        public string $context,
        public ?string $sandbox = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: Wire::string($data['context'] ?? null),
            sandbox: isset($data['sandbox']) ? Wire::string($data['sandbox']) : null,
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
        if ($this->sandbox !== null) {
            $data['sandbox'] = $this->sandbox;
        }
        return $data;
    }
}
