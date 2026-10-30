<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class Info
{
    /**
     * @param ?list<Info> $children
     */
    public function __construct(
        public ?array $children,
        public string $clientWindow,
        public string $context,
        public ?string $originalOpener,
        public string $url,
        public string $userContext,
        public ?string $parent = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            children: isset($data['children']) ? Wire::records($data['children'], Info::fromArray(...)) : null,
            clientWindow: Wire::string($data['clientWindow'] ?? null),
            context: Wire::string($data['context'] ?? null),
            originalOpener: isset($data['originalOpener']) ? Wire::string($data['originalOpener']) : null,
            url: Wire::string($data['url'] ?? null),
            userContext: Wire::string($data['userContext'] ?? null),
            parent: isset($data['parent']) ? Wire::string($data['parent']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'children' => Wire::encode($this->children),
            'clientWindow' => $this->clientWindow,
            'context' => $this->context,
            'originalOpener' => $this->originalOpener,
            'url' => $this->url,
            'userContext' => $this->userContext,
        ];
        if ($this->parent !== null) {
            $data['parent'] = $this->parent;
        }
        return $data;
    }
}
