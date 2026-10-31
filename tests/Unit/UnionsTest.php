<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rnscommerce\WebDriver\BiDi\Script\BigIntValue;
use Rnscommerce\WebDriver\BiDi\Script\BooleanValue;
use Rnscommerce\WebDriver\BiDi\Script\LocalValue;
use Rnscommerce\WebDriver\BiDi\Script\NullValue;
use Rnscommerce\WebDriver\BiDi\Script\NumberValue;
use Rnscommerce\WebDriver\BiDi\Script\RemoteObjectReference;
use Rnscommerce\WebDriver\BiDi\Script\SharedReference;
use Rnscommerce\WebDriver\BiDi\Script\StringValue;
use Rnscommerce\WebDriver\BiDi\Script\UndefinedValue;

final class UnionsTest extends TestCase
{
    use WireAssertions;

    /**
     * @return iterable<string, array{class-string, class-string}>
     */
    public static function memberships(): iterable
    {
        $schema = self::json((string) \file_get_contents(\dirname(__DIR__, 2) . '/schema/schema.json'));
        $types = $schema['types'] ?? null;
        self::assertIsArray($types);
        foreach ($types as $union => $node) {
            if (!\is_array($node) || ($node['kind'] ?? null) !== 'union' || !\is_array($node['variants'] ?? null)) {
                continue;
            }
            foreach (\array_filter($node['variants'], \is_string(...)) as $variant) {
                $parent = self::className((string) $union);
                $child = self::className($variant);
                if (\interface_exists($parent) && (\class_exists($child) || \interface_exists($child))) {
                    yield "{$variant} is a {$union}" => [$child, $parent];
                }
            }
        }
    }

    /**
     * @param class-string $variant
     * @param class-string $union
     */
    #[DataProvider('memberships')]
    public function testEachVariantOfAGeneratedUnionIsOneOfIt(string $variant, string $union): void
    {
        self::assertTrue(\is_subclass_of($variant, $union), "{$variant} does not implement {$union}");
    }

    /**
     * @return iterable<string, array{LocalValue, array<string, mixed>}>
     */
    public static function localValues(): iterable
    {
        yield 'a shared reference' => [new SharedReference('f.1.d.2.e.3'), ['sharedId' => 'f.1.d.2.e.3']];
        yield 'a remote object reference' => [new RemoteObjectReference('h'), ['handle' => 'h']];
        yield 'undefined' => [new UndefinedValue(), ['type' => 'undefined']];
        yield 'null' => [new NullValue(), ['type' => 'null']];
        yield 'a string' => [new StringValue('#save'), ['type' => 'string', 'value' => '#save']];
        yield 'a number' => [new NumberValue(2), ['type' => 'number', 'value' => 2]];
        yield 'a boolean' => [new BooleanValue(true), ['type' => 'boolean', 'value' => true]];
        yield 'a bigint' => [new BigIntValue('9007199254740993'), ['type' => 'bigint', 'value' => '9007199254740993']];
    }

    /**
     * @param array<string, mixed> $wire
     */
    #[DataProvider('localValues')]
    public function testReferencesAndPrimitivesAreLocalValues(LocalValue $value, array $wire): void
    {
        self::assertEncodesTo($wire, $value);
    }

    /**
     * @return class-string
     */
    private static function className(string $type): string
    {
        [$module, $name] = \explode('.', $type, 2);
        /** @var class-string */
        return 'Rnscommerce\\WebDriver\\BiDi\\' . \ucfirst($module) . '\\' . $name;
    }
}
