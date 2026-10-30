<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class NodeProperties
{
    /**
     * @param ?array<string, string> $attributes
     * @param ?list<NodeRemoteValue> $children
     */
    public function __construct(
        public int $nodeType,
        public int $childNodeCount,
        public ?array $attributes = null,
        public ?array $children = null,
        public ?string $localName = null,
        public ?NodePropertiesMode $mode = null,
        public ?string $namespaceURI = null,
        public ?string $nodeValue = null,
        public ?NodeRemoteValue $shadowRoot = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            nodeType: Wire::int($data['nodeType'] ?? null),
            childNodeCount: Wire::int($data['childNodeCount'] ?? null),
            attributes: isset($data['attributes']) ? Wire::dict($data['attributes'], Wire::string(...)) : null,
            children: isset($data['children'])
                ? Wire::records($data['children'], NodeRemoteValue::fromArray(...))
                : null,
            localName: isset($data['localName']) ? Wire::string($data['localName']) : null,
            mode: isset($data['mode']) ? NodePropertiesMode::from(Wire::string($data['mode'])) : null,
            namespaceURI: isset($data['namespaceURI']) ? Wire::string($data['namespaceURI']) : null,
            nodeValue: isset($data['nodeValue']) ? Wire::string($data['nodeValue']) : null,
            shadowRoot: isset($data['shadowRoot']) ? NodeRemoteValue::fromArray(Wire::map($data['shadowRoot'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'nodeType' => $this->nodeType,
            'childNodeCount' => $this->childNodeCount,
        ];
        if ($this->attributes !== null) {
            $data['attributes'] = Wire::encode($this->attributes);
        }
        if ($this->children !== null) {
            $data['children'] = Wire::encode($this->children);
        }
        if ($this->localName !== null) {
            $data['localName'] = $this->localName;
        }
        if ($this->mode !== null) {
            $data['mode'] = $this->mode->value;
        }
        if ($this->namespaceURI !== null) {
            $data['namespaceURI'] = $this->namespaceURI;
        }
        if ($this->nodeValue !== null) {
            $data['nodeValue'] = $this->nodeValue;
        }
        if ($this->shadowRoot !== null) {
            $data['shadowRoot'] = $this->shadowRoot->toArray();
        }
        return $data;
    }
}
