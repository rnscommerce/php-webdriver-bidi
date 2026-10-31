<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class StopScreencastResult
{
    public function __construct(
        public readonly string $path,
        public readonly ?string $error = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            path: Wire::string($data['path'] ?? null),
            error: isset($data['error']) ? Wire::string($data['error']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'path' => $this->path,
        ];
        if ($this->error !== null) {
            $data['error'] = $this->error;
        }
        return $data;
    }
}
