<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Browser;

use Rnscommerce\WebDriver\BiDi\Session\ProxyConfiguration;
use Rnscommerce\WebDriver\BiDi\Session\ProxyConfigurationFactory;
use Rnscommerce\WebDriver\BiDi\Session\UserPromptHandler;
use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class CreateUserContextParameters
{
    public function __construct(
        public ?bool $acceptInsecureCerts = null,
        public ?ProxyConfiguration $proxy = null,
        public ?UserPromptHandler $unhandledPromptBehavior = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            acceptInsecureCerts: isset($data['acceptInsecureCerts']) ? Wire::bool($data['acceptInsecureCerts']) : null,
            proxy: isset($data['proxy']) ? ProxyConfigurationFactory::fromArray(Wire::map($data['proxy'])) : null,
            unhandledPromptBehavior: isset($data['unhandledPromptBehavior'])
                ? UserPromptHandler::fromArray(Wire::map($data['unhandledPromptBehavior']))
                : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->acceptInsecureCerts !== null) {
            $data['acceptInsecureCerts'] = $this->acceptInsecureCerts;
        }
        if ($this->proxy !== null) {
            $data['proxy'] = $this->proxy->toArray();
        }
        if ($this->unhandledPromptBehavior !== null) {
            $data['unhandledPromptBehavior'] = $this->unhandledPromptBehavior->toArray();
        }
        return $data;
    }
}
