<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

use UnexpectedValueException;

/**
 * Narrows decoded JSON to the types the generated records declare.
 *
 * @internal
 */
final class Wire
{
    public static function string(mixed $value): string
    {
        return \is_string($value) ? $value : throw self::unexpected('a string', $value);
    }

    public static function int(mixed $value): int
    {
        return \is_int($value) ? $value : throw self::unexpected('an integer', $value);
    }

    public static function number(mixed $value): int|float
    {
        return \is_int($value) || \is_float($value) ? $value : throw self::unexpected('a number', $value);
    }

    public static function bool(mixed $value): bool
    {
        return \is_bool($value) ? $value : throw self::unexpected('a boolean', $value);
    }

    /**
     * @return array<string, mixed>
     */
    public static function map(mixed $value): array
    {
        if (!\is_array($value)) {
            throw self::unexpected('an object', $value);
        }
        /** @var array<string, mixed> $value */
        return $value;
    }

    /**
     * @template T
     * @param \Closure(mixed): T $item
     * @return list<T>
     */
    public static function list(mixed $value, \Closure $item): array
    {
        if (!\is_array($value) || !\array_is_list($value)) {
            throw self::unexpected('an array', $value);
        }
        return \array_map($item, $value);
    }

    /**
     * @template T
     * @param \Closure(array<string, mixed>): T $from
     * @return list<T>
     */
    public static function records(mixed $value, \Closure $from): array
    {
        return self::list($value, static fn (mixed $item) => $from(self::map($item)));
    }

    /**
     * @template T
     * @param \Closure(mixed): T $item
     * @return array<string, T>
     */
    public static function dict(mixed $value, \Closure $item): array
    {
        return \array_map($item, self::map($value));
    }

    /**
     * Encodes what the generated types hold, a record as an object even when none of its fields is set.
     */
    public static function encode(mixed $value): mixed
    {
        return match (true) {
            $value instanceof \BackedEnum => $value->value,
            \is_object($value) && \method_exists($value, 'toArray') => (object) $value->toArray(),
            \is_array($value) => \array_map(self::encode(...), $value),
            default => $value,
        };
    }

    /**
     * @param array<string, mixed> $map
     */
    public static function encodeMap(array $map): \stdClass
    {
        return (object) \array_map(self::encode(...), $map);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function unknown(string $union, array $data): UnexpectedValueException
    {
        return new UnexpectedValueException(
            \sprintf('Cannot tell which %s this is: %s', $union, \json_encode($data)),
        );
    }

    private static function unexpected(string $expected, mixed $value): UnexpectedValueException
    {
        return new UnexpectedValueException(\sprintf('Expected %s, got %s', $expected, \get_debug_type($value)));
    }
}
