<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class UserPromptHandler
{
    public function __construct(
        public ?UserPromptHandlerType $alert = null,
        public ?UserPromptHandlerType $beforeUnload = null,
        public ?UserPromptHandlerType $confirm = null,
        public ?UserPromptHandlerType $default = null,
        public ?UserPromptHandlerType $file = null,
        public ?UserPromptHandlerType $prompt = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            alert: isset($data['alert']) ? UserPromptHandlerType::from(Wire::string($data['alert'])) : null,
            beforeUnload: isset($data['beforeUnload'])
                ? UserPromptHandlerType::from(Wire::string($data['beforeUnload']))
                : null,
            confirm: isset($data['confirm']) ? UserPromptHandlerType::from(Wire::string($data['confirm'])) : null,
            default: isset($data['default']) ? UserPromptHandlerType::from(Wire::string($data['default'])) : null,
            file: isset($data['file']) ? UserPromptHandlerType::from(Wire::string($data['file'])) : null,
            prompt: isset($data['prompt']) ? UserPromptHandlerType::from(Wire::string($data['prompt'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [];
        if ($this->alert !== null) {
            $data['alert'] = $this->alert->value;
        }
        if ($this->beforeUnload !== null) {
            $data['beforeUnload'] = $this->beforeUnload->value;
        }
        if ($this->confirm !== null) {
            $data['confirm'] = $this->confirm->value;
        }
        if ($this->default !== null) {
            $data['default'] = $this->default->value;
        }
        if ($this->file !== null) {
            $data['file'] = $this->file->value;
        }
        if ($this->prompt !== null) {
            $data['prompt'] = $this->prompt->value;
        }
        return $data;
    }
}
