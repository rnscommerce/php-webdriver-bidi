<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final class RequestData
{
    /**
     * @param list<Header> $headers
     * @param list<Cookie> $cookies
     */
    public function __construct(
        public readonly string $request,
        public readonly string $url,
        public readonly string $method,
        public readonly array $headers,
        public readonly array $cookies,
        public readonly int $headersSize,
        public readonly ?int $bodySize,
        public readonly string $destination,
        public readonly ?string $initiatorType,
        public readonly FetchTimingInfo $timings,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            request: Wire::string($data['request'] ?? null),
            url: Wire::string($data['url'] ?? null),
            method: Wire::string($data['method'] ?? null),
            headers: Wire::records($data['headers'] ?? null, Header::fromArray(...)),
            cookies: Wire::records($data['cookies'] ?? null, Cookie::fromArray(...)),
            headersSize: Wire::int($data['headersSize'] ?? null),
            bodySize: isset($data['bodySize']) ? Wire::int($data['bodySize']) : null,
            destination: Wire::string($data['destination'] ?? null),
            initiatorType: isset($data['initiatorType']) ? Wire::string($data['initiatorType']) : null,
            timings: FetchTimingInfo::fromArray(Wire::map($data['timings'] ?? null)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'request' => $this->request,
            'url' => $this->url,
            'method' => $this->method,
            'headers' => Wire::encode($this->headers),
            'cookies' => Wire::encode($this->cookies),
            'headersSize' => $this->headersSize,
            'bodySize' => $this->bodySize,
            'destination' => $this->destination,
            'initiatorType' => $this->initiatorType,
            'timings' => Wire::encode($this->timings),
        ];
    }
}
