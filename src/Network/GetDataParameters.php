<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class GetDataParameters
{
    public function __construct(
        public DataType $dataType,
        public string $request,
        public ?string $collector = null,
        public ?bool $disown = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            dataType: DataType::from(Wire::string($data['dataType'] ?? null)),
            request: Wire::string($data['request'] ?? null),
            collector: isset($data['collector']) ? Wire::string($data['collector']) : null,
            disown: isset($data['disown']) ? Wire::bool($data['disown']) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'dataType' => $this->dataType->value,
            'request' => $this->request,
        ];
        if ($this->collector !== null) {
            $data['collector'] = $this->collector;
        }
        if ($this->disown !== null) {
            $data['disown'] = $this->disown;
        }
        return $data;
    }
}
