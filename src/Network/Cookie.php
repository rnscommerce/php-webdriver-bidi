<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final class Cookie
{
    public function __construct(
        public readonly string $name,
        public readonly BytesValue $value,
        public readonly string $domain,
        public readonly string $path,
        public readonly int $size,
        public readonly bool $httpOnly,
        public readonly bool $secure,
        public readonly SameSite $sameSite,
        public readonly ?int $expiry = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: Wire::string($data['name'] ?? null),
            value: BytesValueFactory::fromArray(Wire::map($data['value'] ?? null)),
            domain: Wire::string($data['domain'] ?? null),
            path: Wire::string($data['path'] ?? null),
            size: Wire::int($data['size'] ?? null),
            httpOnly: Wire::bool($data['httpOnly'] ?? null),
            secure: Wire::bool($data['secure'] ?? null),
            sameSite: SameSite::from(Wire::string($data['sameSite'] ?? null)),
            expiry: isset($data['expiry']) ? Wire::int($data['expiry']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'name' => $this->name,
            'value' => Wire::encode($this->value),
            'domain' => $this->domain,
            'path' => $this->path,
            'size' => $this->size,
            'httpOnly' => $this->httpOnly,
            'secure' => $this->secure,
            'sameSite' => $this->sameSite->value,
        ];
        if ($this->expiry !== null) {
            $data['expiry'] = $this->expiry;
        }
        return $data;
    }
}
