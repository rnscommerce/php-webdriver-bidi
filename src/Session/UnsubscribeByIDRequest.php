<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final class UnsubscribeByIDRequest implements UnsubscribeParameters
{
    /**
     * @param list<string> $subscriptions
     */
    public function __construct(
        public readonly array $subscriptions,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            subscriptions: Wire::list($data['subscriptions'] ?? null, Wire::string(...)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'subscriptions' => Wire::encode($this->subscriptions),
        ];
    }
}
