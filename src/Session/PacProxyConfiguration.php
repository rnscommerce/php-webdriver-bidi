<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class PacProxyConfiguration implements ProxyConfiguration
{
    public function __construct(
        public string $proxyAutoconfigUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            proxyAutoconfigUrl: Wire::string($data['proxyAutoconfigUrl'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'proxyType' => 'pac',
            'proxyAutoconfigUrl' => $this->proxyAutoconfigUrl,
        ];
    }
}
