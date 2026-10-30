<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class FetchTimingInfo
{
    public function __construct(
        public int|float $timeOrigin,
        public int|float $requestTime,
        public int|float $redirectStart,
        public int|float $redirectEnd,
        public int|float $fetchStart,
        public int|float $dnsStart,
        public int|float $dnsEnd,
        public int|float $connectStart,
        public int|float $connectEnd,
        public int|float $tlsStart,
        public int|float $requestStart,
        public int|float $responseStart,
        public int|float $responseEnd,
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
