<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class AddDataCollectorParameters
{
    /**
     * @param list<DataType> $dataTypes
     * @param ?list<string> $contexts
     * @param ?list<string> $userContexts
     */
    public function __construct(
        public array $dataTypes,
        public int $maxEncodedDataSize,
        public ?CollectorType $collectorType = null,
        public ?array $contexts = null,
        public ?array $userContexts = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dataTypes: Wire::list(
                $data['dataTypes'] ?? null,
                static fn (mixed $item): DataType => DataType::from(Wire::string($item)),
            ),
            maxEncodedDataSize: Wire::int($data['maxEncodedDataSize'] ?? null),
            collectorType: isset($data['collectorType'])
                ? CollectorType::from(Wire::string($data['collectorType']))
                : null,
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
            'dataTypes' => Wire::encode($this->dataTypes),
            'maxEncodedDataSize' => $this->maxEncodedDataSize,
        ];
        if ($this->collectorType !== null) {
            $data['collectorType'] = $this->collectorType->value;
        }
        if ($this->contexts !== null) {
            $data['contexts'] = Wire::encode($this->contexts);
        }
        if ($this->userContexts !== null) {
            $data['userContexts'] = Wire::encode($this->userContexts);
        }
        return $data;
    }
}
