<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class Source
{
    public function __construct(
        public readonly string $realm,
        public readonly ?string $context = null,
        public readonly ?string $userContext = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            realm: Wire::string($data['realm'] ?? null),
            context: isset($data['context']) ? Wire::string($data['context']) : null,
            userContext: isset($data['userContext']) ? Wire::string($data['userContext']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'realm' => $this->realm,
        ];
        if ($this->context !== null) {
            $data['context'] = $this->context;
        }
        if ($this->userContext !== null) {
            $data['userContext'] = $this->userContext;
        }
        return $data;
    }
}
