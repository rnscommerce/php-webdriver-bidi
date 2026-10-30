<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class StartScreencastResult
{
    public function __construct(
        public string $screencast,
        public string $path,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            screencast: Wire::string($data['screencast'] ?? null),
            path: Wire::string($data['path'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'screencast' => $this->screencast,
            'path' => $this->path,
        ];
    }
}
