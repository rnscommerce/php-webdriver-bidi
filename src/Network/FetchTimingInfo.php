<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final class FetchTimingInfo
{
    public function __construct(
        public readonly int|float $timeOrigin,
        public readonly int|float $requestTime,
        public readonly int|float $redirectStart,
        public readonly int|float $redirectEnd,
        public readonly int|float $fetchStart,
        public readonly int|float $dnsStart,
        public readonly int|float $dnsEnd,
        public readonly int|float $connectStart,
        public readonly int|float $connectEnd,
        public readonly int|float $tlsStart,
        public readonly int|float $requestStart,
        public readonly int|float $responseStart,
        public readonly int|float $responseEnd,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            timeOrigin: Wire::number($data['timeOrigin'] ?? null),
            requestTime: Wire::number($data['requestTime'] ?? null),
            redirectStart: Wire::number($data['redirectStart'] ?? null),
            redirectEnd: Wire::number($data['redirectEnd'] ?? null),
            fetchStart: Wire::number($data['fetchStart'] ?? null),
            dnsStart: Wire::number($data['dnsStart'] ?? null),
            dnsEnd: Wire::number($data['dnsEnd'] ?? null),
            connectStart: Wire::number($data['connectStart'] ?? null),
            connectEnd: Wire::number($data['connectEnd'] ?? null),
            tlsStart: Wire::number($data['tlsStart'] ?? null),
            requestStart: Wire::number($data['requestStart'] ?? null),
            responseStart: Wire::number($data['responseStart'] ?? null),
            responseEnd: Wire::number($data['responseEnd'] ?? null),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'timeOrigin' => $this->timeOrigin,
            'requestTime' => $this->requestTime,
            'redirectStart' => $this->redirectStart,
            'redirectEnd' => $this->redirectEnd,
            'fetchStart' => $this->fetchStart,
            'dnsStart' => $this->dnsStart,
            'dnsEnd' => $this->dnsEnd,
            'connectStart' => $this->connectStart,
            'connectEnd' => $this->connectEnd,
            'tlsStart' => $this->tlsStart,
            'requestStart' => $this->requestStart,
            'responseStart' => $this->responseStart,
            'responseEnd' => $this->responseEnd,
        ];
    }
}
