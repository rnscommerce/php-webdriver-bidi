<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final class ProxyConfigurationFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): ProxyConfiguration
    {
        return match ($data['proxyType'] ?? null) {
            'autodetect' => AutodetectProxyConfiguration::fromArray($data),
            'direct' => DirectProxyConfiguration::fromArray($data),
            'manual' => ManualProxyConfiguration::fromArray($data),
            'pac' => PacProxyConfiguration::fromArray($data),
            'system' => SystemProxyConfiguration::fromArray($data),
            default => throw Wire::unknown('ProxyConfiguration', $data),
        };
    }
}
