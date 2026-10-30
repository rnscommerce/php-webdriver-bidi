<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class RemoteValueFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): RemoteValue
    {
        return match ($data['type'] ?? null) {
            'undefined' => UndefinedValue::fromArray($data),
            'null' => NullValue::fromArray($data),
            'string' => StringValue::fromArray($data),
            'number' => NumberValue::fromArray($data),
            'boolean' => BooleanValue::fromArray($data),
            'bigint' => BigIntValue::fromArray($data),
            'symbol' => SymbolRemoteValue::fromArray($data),
            'array' => ArrayRemoteValue::fromArray($data),
            'object' => ObjectRemoteValue::fromArray($data),
            'function' => FunctionRemoteValue::fromArray($data),
            'regexp' => RegExpRemoteValue::fromArray($data),
            'date' => DateRemoteValue::fromArray($data),
            'map' => MapRemoteValue::fromArray($data),
            'set' => SetRemoteValue::fromArray($data),
            'weakmap' => WeakMapRemoteValue::fromArray($data),
            'weakset' => WeakSetRemoteValue::fromArray($data),
            'generator' => GeneratorRemoteValue::fromArray($data),
            'error' => ErrorRemoteValue::fromArray($data),
            'proxy' => ProxyRemoteValue::fromArray($data),
            'promise' => PromiseRemoteValue::fromArray($data),
            'typedarray' => TypedArrayRemoteValue::fromArray($data),
            'arraybuffer' => ArrayBufferRemoteValue::fromArray($data),
            'nodelist' => NodeListRemoteValue::fromArray($data),
            'htmlcollection' => HTMLCollectionRemoteValue::fromArray($data),
            'node' => NodeRemoteValue::fromArray($data),
            'window' => WindowProxyRemoteValue::fromArray($data),
            default => throw Wire::unknown('RemoteValue', $data),
        };
    }
}
