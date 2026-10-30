<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class AddPreloadScriptParameters
{
    /**
     * @param ?list<ChannelValue> $arguments
     * @param ?list<string> $contexts
     * @param ?list<string> $userContexts
     */
    public function __construct(
        public string $functionDeclaration,
        public ?array $arguments = null,
        public ?array $contexts = null,
        public ?array $userContexts = null,
        public ?string $sandbox = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            functionDeclaration: Wire::string($data['functionDeclaration'] ?? null),
            arguments: isset($data['arguments'])
                ? Wire::records($data['arguments'], ChannelValue::fromArray(...))
                : null,
            contexts: isset($data['contexts']) ? Wire::list($data['contexts'], Wire::string(...)) : null,
            userContexts: isset($data['userContexts']) ? Wire::list($data['userContexts'], Wire::string(...)) : null,
            sandbox: isset($data['sandbox']) ? Wire::string($data['sandbox']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'functionDeclaration' => $this->functionDeclaration,
        ];
        if ($this->arguments !== null) {
            $data['arguments'] = Wire::encode($this->arguments);
        }
        if ($this->contexts !== null) {
            $data['contexts'] = Wire::encode($this->contexts);
        }
        if ($this->userContexts !== null) {
            $data['userContexts'] = Wire::encode($this->userContexts);
        }
        if ($this->sandbox !== null) {
            $data['sandbox'] = $this->sandbox;
        }
        return $data;
    }
}
