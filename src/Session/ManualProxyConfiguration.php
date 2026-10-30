<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ManualProxyConfiguration implements ProxyConfiguration
{
    /**
     * @param ?list<string> $noProxy
     */
    public function __construct(
        public string $socksProxy,
        public int $socksVersion,
        public ?string $httpProxy = null,
        public ?string $sslProxy = null,
        public ?array $noProxy = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            socksProxy: Wire::string($data['socksProxy'] ?? null),
            socksVersion: Wire::int($data['socksVersion'] ?? null),
            httpProxy: isset($data['httpProxy']) ? Wire::string($data['httpProxy']) : null,
            sslProxy: isset($data['sslProxy']) ? Wire::string($data['sslProxy']) : null,
            noProxy: isset($data['noProxy']) ? Wire::list($data['noProxy'], Wire::string(...)) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'proxyType' => 'manual',
            'socksProxy' => $this->socksProxy,
            'socksVersion' => $this->socksVersion,
        ];
        if ($this->httpProxy !== null) {
            $data['httpProxy'] = $this->httpProxy;
        }
        if ($this->sslProxy !== null) {
            $data['sslProxy'] = $this->sslProxy;
        }
        if ($this->noProxy !== null) {
            $data['noProxy'] = Wire::encode($this->noProxy);
        }
        return $data;
    }
}
