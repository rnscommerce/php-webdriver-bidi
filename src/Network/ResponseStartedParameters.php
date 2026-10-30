<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ResponseStartedParameters
{
    /**
     * @param ?list<string> $intercepts
     */
    public function __construct(
        public ?string $context,
        public bool $isBlocked,
        public ?string $navigation,
        public int $redirectCount,
        public RequestData $request,
        public int $timestamp,
        public ResponseData $response,
        public ?string $userContext = null,
        public ?array $intercepts = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: isset($data['context']) ? Wire::string($data['context']) : null,
            isBlocked: Wire::bool($data['isBlocked'] ?? null),
            navigation: isset($data['navigation']) ? Wire::string($data['navigation']) : null,
            redirectCount: Wire::int($data['redirectCount'] ?? null),
            request: RequestData::fromArray(Wire::map($data['request'] ?? null)),
            timestamp: Wire::int($data['timestamp'] ?? null),
            response: ResponseData::fromArray(Wire::map($data['response'] ?? null)),
            userContext: isset($data['userContext']) ? Wire::string($data['userContext']) : null,
            intercepts: isset($data['intercepts']) ? Wire::list($data['intercepts'], Wire::string(...)) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'context' => $this->context,
            'isBlocked' => $this->isBlocked,
            'navigation' => $this->navigation,
            'redirectCount' => $this->redirectCount,
            'request' => $this->request->toArray(),
            'timestamp' => $this->timestamp,
            'response' => $this->response->toArray(),
        ];
        if ($this->userContext !== null) {
            $data['userContext'] = $this->userContext;
        }
        if ($this->intercepts !== null) {
            $data['intercepts'] = Wire::encode($this->intercepts);
        }
        return $data;
    }
}
