<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final class SubscribeParameters
{
    /**
     * @param list<string> $events
     * @param ?list<string> $contexts
     * @param ?list<string> $userContexts
     */
    public function __construct(
        public readonly array $events,
        public readonly ?array $contexts = null,
        public readonly ?array $userContexts = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            events: Wire::list($data['events'] ?? null, Wire::string(...)),
            contexts: isset($data['contexts']) ? Wire::list($data['contexts'], Wire::string(...)) : null,
            userContexts: isset($data['userContexts']) ? Wire::list($data['userContexts'], Wire::string(...)) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'events' => Wire::encode($this->events),
        ];
        if ($this->contexts !== null) {
            $data['contexts'] = Wire::encode($this->contexts);
        }
        if ($this->userContexts !== null) {
            $data['userContexts'] = Wire::encode($this->userContexts);
        }
        return $data;
    }
}
