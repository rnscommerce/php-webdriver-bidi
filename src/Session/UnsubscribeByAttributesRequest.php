<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class UnsubscribeByAttributesRequest implements UnsubscribeParameters
{
    /**
     * @param list<string> $events
     */
    public function __construct(
        public array $events,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            events: Wire::list($data['events'] ?? null, Wire::string(...)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'events' => Wire::encode($this->events),
        ];
    }
}
