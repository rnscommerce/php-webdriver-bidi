<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

use Rnscommerce\WebDriver\BiDi\Wire;

final class CreateParameters
{
    public function __construct(
        public readonly CreateType $type,
        public readonly ?string $referenceContext = null,
        public readonly ?bool $background = null,
        public readonly ?string $userContext = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            type: CreateType::from(Wire::string($data['type'] ?? null)),
            referenceContext: isset($data['referenceContext']) ? Wire::string($data['referenceContext']) : null,
            background: isset($data['background']) ? Wire::bool($data['background']) : null,
            userContext: isset($data['userContext']) ? Wire::string($data['userContext']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'type' => $this->type->value,
        ];
        if ($this->referenceContext !== null) {
            $data['referenceContext'] = $this->referenceContext;
        }
        if ($this->background !== null) {
            $data['background'] = $this->background;
        }
        if ($this->userContext !== null) {
            $data['userContext'] = $this->userContext;
        }
        return $data;
    }
}
