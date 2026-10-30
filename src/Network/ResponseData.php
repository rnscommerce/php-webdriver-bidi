<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ResponseData
{
    /**
     * @param list<Header> $headers
     * @param ?list<AuthChallenge> $authChallenges
     */
    public function __construct(
        public string $url,
        public string $protocol,
        public int $status,
        public string $statusText,
        public bool $fromCache,
        public array $headers,
        public string $mimeType,
        public int $bytesReceived,
        public ?int $headersSize,
        public ?int $bodySize,
        public ResponseContent $content,
        public ?array $authChallenges = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            url: Wire::string($data['url'] ?? null),
            protocol: Wire::string($data['protocol'] ?? null),
            status: Wire::int($data['status'] ?? null),
            statusText: Wire::string($data['statusText'] ?? null),
            fromCache: Wire::bool($data['fromCache'] ?? null),
            headers: Wire::records($data['headers'] ?? null, Header::fromArray(...)),
            mimeType: Wire::string($data['mimeType'] ?? null),
            bytesReceived: Wire::int($data['bytesReceived'] ?? null),
            headersSize: isset($data['headersSize']) ? Wire::int($data['headersSize']) : null,
            bodySize: isset($data['bodySize']) ? Wire::int($data['bodySize']) : null,
            content: ResponseContent::fromArray(Wire::map($data['content'] ?? null)),
            authChallenges: isset($data['authChallenges'])
                ? Wire::records($data['authChallenges'], AuthChallenge::fromArray(...))
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'url' => $this->url,
            'protocol' => $this->protocol,
            'status' => $this->status,
            'statusText' => $this->statusText,
            'fromCache' => $this->fromCache,
            'headers' => Wire::encode($this->headers),
            'mimeType' => $this->mimeType,
            'bytesReceived' => $this->bytesReceived,
            'headersSize' => $this->headersSize,
            'bodySize' => $this->bodySize,
            'content' => $this->content->toArray(),
        ];
        if ($this->authChallenges !== null) {
            $data['authChallenges'] = Wire::encode($this->authChallenges);
        }
        return $data;
    }
}
