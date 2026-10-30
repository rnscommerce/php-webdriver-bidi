<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class NavigationInfo
{
    public function __construct(
        public string $context,
        public ?string $navigation,
        public int $timestamp,
        public string $url,
        public ?string $userContext = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            context: Wire::string($data['context'] ?? null),
            navigation: isset($data['navigation']) ? Wire::string($data['navigation']) : null,
            timestamp: Wire::int($data['timestamp'] ?? null),
            url: Wire::string($data['url'] ?? null),
            userContext: isset($data['userContext']) ? Wire::string($data['userContext']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'context' => $this->context,
            'navigation' => $this->navigation,
            'timestamp' => $this->timestamp,
            'url' => $this->url,
        ];
        if ($this->userContext !== null) {
            $data['userContext'] = $this->userContext;
        }
        return $data;
    }
}
