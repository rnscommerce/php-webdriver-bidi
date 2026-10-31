<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class CallFunctionParameters
{
    /**
     * @param ?list<LocalValue> $arguments
     */
    public function __construct(
        public readonly string $functionDeclaration,
        public readonly bool $awaitPromise,
        public readonly Target $target,
        public readonly ?array $arguments = null,
        public readonly ?ResultOwnership $resultOwnership = null,
        public readonly ?SerializationOptions $serializationOptions = null,
        public readonly ?LocalValue $this_ = null,
        public readonly ?bool $userActivation = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            functionDeclaration: Wire::string($data['functionDeclaration'] ?? null),
            awaitPromise: Wire::bool($data['awaitPromise'] ?? null),
            target: TargetFactory::fromArray(Wire::map($data['target'] ?? null)),
            arguments: isset($data['arguments'])
                ? Wire::records($data['arguments'], LocalValueFactory::fromArray(...))
                : null,
            resultOwnership: isset($data['resultOwnership'])
                ? ResultOwnership::from(Wire::string($data['resultOwnership']))
                : null,
            serializationOptions: isset($data['serializationOptions'])
                ? SerializationOptions::fromArray(Wire::map($data['serializationOptions']))
                : null,
            this_: isset($data['this']) ? LocalValueFactory::fromArray(Wire::map($data['this'])) : null,
            userActivation: isset($data['userActivation']) ? Wire::bool($data['userActivation']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'functionDeclaration' => $this->functionDeclaration,
            'awaitPromise' => $this->awaitPromise,
            'target' => Wire::encode($this->target),
        ];
        if ($this->arguments !== null) {
            $data['arguments'] = Wire::encode($this->arguments);
        }
        if ($this->resultOwnership !== null) {
            $data['resultOwnership'] = $this->resultOwnership->value;
        }
        if ($this->serializationOptions !== null) {
            $data['serializationOptions'] = Wire::encode($this->serializationOptions);
        }
        if ($this->this_ !== null) {
            $data['this'] = Wire::encode($this->this_);
        }
        if ($this->userActivation !== null) {
            $data['userActivation'] = $this->userActivation;
        }
        return $data;
    }
}
