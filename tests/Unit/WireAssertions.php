<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Unit;

use Rnscommerce\WebDriver\BiDi\Wire;

trait WireAssertions
{
    /**
     * @return array<string, mixed>
     */
    private static function json(string $json): array
    {
        $decoded = \json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
        self::assertIsArray($decoded);
        /** @var array<string, mixed> $decoded */
        return $decoded;
    }

    /**
     * Asserts that what was read encodes back to the message it was read from, in any key order.
     *
     * @param array<string, mixed> $expected
     */
    private static function assertEncodesTo(array $expected, mixed $actual): void
    {
        self::assertSame(self::sorted($expected), self::sorted(Wire::encode($actual)));
    }

    private static function sorted(mixed $value): mixed
    {
        if (!\is_array($value)) {
            return $value;
        }
        $value = \array_map(self::sorted(...), $value);
        if (!\array_is_list($value)) {
            \ksort($value);
        }
        return $value;
    }
}
