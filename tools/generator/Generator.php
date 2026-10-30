<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Generator;

use RuntimeException;

/**
 * Writes the PHP for the allowlisted commands and events of Selenium's BiDi schema, and for every
 * type they reach.
 *
 * @phpstan-type Node array<string, mixed>
 * @phpstan-type Field array{name: string, wire: string, required: bool, type: Node}
 */
final class Generator
{
    public const HEADER = 'Generated from schema/schema.json — do not edit, run composer generate.';

    private const ROOT = 'Rnscommerce\\WebDriver\\BiDi';
    private const WIRE = '{{Rnscommerce\\WebDriver\\BiDi\\Wire}}';
    private const COMMAND = '{{Rnscommerce\\WebDriver\\BiDi\\Command}}';
    private const EVENT = '{{Rnscommerce\\WebDriver\\BiDi\\Event}}';
    private const PRIMITIVES = [
        'string' => 'string',
        'integer' => 'int',
        'number' => 'int|float',
        'boolean' => 'bool',
        'any' => 'mixed',
    ];
    private const NARROWERS = ['string' => 'string', 'integer' => 'int', 'number' => 'number', 'boolean' => 'bool'];
    private const GUARDS = [
        'string' => '\\is_string(%s)',
        'integer' => '\\is_int(%s)',
        'number' => '\\is_int(%1$s) || \\is_float(%1$s)',
        'boolean' => '\\is_bool(%s)',
    ];

    /** @var array<string, Node> */
    private array $types;
    /** @var list<Node> */
    private array $commands;
    /** @var list<Node> */
    private array $events;
    /** @var list<string> */
    private array $reachable;

    /**
     * @param Node $schema
     * @param array{commands: list<string>, events: list<string>} $allowlist
     */
    public function __construct(array $schema, array $allowlist)
    {
        $this->types = \array_map(self::node(...), self::node($schema['types'] ?? null));
        $this->commands = self::pick(self::nodes($schema, 'commands'), $allowlist['commands']);
        $this->events = self::pick(self::nodes($schema, 'events'), $allowlist['events']);
        $this->reachable = $this->reachable();
    }

    /**
     * @return array<string, string> each file's path under src/, and its contents
     */
    public function generate(): array
    {
        $files = [];
        $domains = \array_unique(\array_map(
            static fn (array $entry): string => self::string($entry, 'domain'),
            [...$this->commands, ...$this->events],
        ));
        foreach ($domains as $domain) {
            $files += $this->module($domain);
        }
        foreach ($this->reachable as $name) {
            $node = $this->type($name);
            $files += match (self::string($node, 'kind')) {
                'record' => isset($node['map']) ? [] : $this->record($name, $node),
                'enum' => $this->enum($name, $node),
                'union' => $this->union($name, $node),
                default => [],
            };
        }
        \ksort($files);
        return $files;
    }

    /**
     * @return list<string>
     */
    private function reachable(): array
    {
        $queue = [];
        foreach ([...$this->commands, ...$this->events] as $entry) {
            $queue = [...$queue, ...self::refs($entry['params'] ?? null), ...self::refs($entry['result'] ?? null)];
        }
        $seen = [];
        while ($queue !== []) {
            $name = \array_shift($queue);
            if (isset($seen[$name])) {
                continue;
            }
            $seen[$name] = true;
            $node = $this->type($name);
            $queue = [...$queue, ...self::refs($node), ...self::strings($node, 'variants')];
            $default = self::optionalString(self::node($node['selector'] ?? []), 'default');
            if ($default !== null) {
                $queue[] = $default;
            }
        }
        $names = \array_keys($seen);
        \sort($names);
        return $names;
    }

    /**
     * @return array<string, string>
     */
    private function module(string $domain): array
    {
        $methods = [];
        $eventClass = self::EVENT;
        $commandClass = self::COMMAND;
        foreach ($this->events as $event) {
            if (self::string($event, 'domain') !== $domain) {
                continue;
            }
            $params = $this->resolve(self::node($event['params'] ?? null));
            $name = self::string($event, 'name');
            $method = self::string($event, 'method');
            $methods[] = <<<PHP
                    /**
                     * @return {$eventClass}<{$this->phpType($params)}>
                     */
                    public static function {$name}(): {$eventClass}
                    {
                        return new {$eventClass}('{$method}', {$this->reader($params)}::fromArray(...));
                    }
                PHP;
        }
        foreach ($this->commands as $command) {
            if (self::string($command, 'domain') !== $domain) {
                continue;
            }
            $params = $this->resolve(self::node($command['params'] ?? null));
            $name = self::string($command, 'name');
            $method = self::string($command, 'method');
            $result = isset($command['result']) ? $this->resolve(self::node($command['result'])) : null;
            $resultType = $result === null ? 'null' : $this->phpType($result);
            $mapper = $result === null
                ? 'static fn (array $result): null => null'
                : $this->reader($result) . '::fromArray(...)';
            $methods[] = <<<PHP
                    /**
                     * @return {$commandClass}<{$resultType}>
                     */
                    public static function {$name}({$this->phpType($params)} \$params): {$commandClass}
                    {
                        return new {$commandClass}(
                            '{$method}',
                            \$params->toArray(),
                            {$mapper},
                        );
                    }
                PHP;
        }
        return $this->file(self::ROOT . '\\Protocol\\Module\\' . \ucfirst($domain), 'final class', $methods);
    }

    /**
     * @param Node $node
     * @return array<string, string>
     */
    private function record(string $name, array $node): array
    {
        $fields = \array_map($this->field(...), self::nodes($node, 'fields'));
        $properties = \array_values(\array_filter(
            $fields,
            static fn (array $field): bool => !\array_key_exists('const', $field['type']),
        ));
        \usort($properties, static fn (array $a, array $b): int => $b['required'] <=> $a['required']);

        $methods = [];
        if ($properties !== []) {
            $docs = '';
            $params = '';
            foreach ($properties as $property) {
                $nullable = !$property['required'] || self::isNullable($property['type']);
                $php = $this->phpType($property['type']);
                $doc = $this->docType($property['type']);
                if ($nullable) {
                    $php = self::orNull($php);
                    $doc = self::orNull($doc);
                }
                if ($doc !== $php) {
                    $docs .= "     * @param {$doc} \${$property['name']}\n";
                }
                $default = $property['required'] ? '' : ' = null';
                $params .= "        public {$php} \${$property['name']}{$default},\n";
            }
            $docs = $docs === '' ? '' : "    /**\n{$docs}     */\n";
            $methods[] = "{$docs}    public function __construct(\n{$params}    ) {\n    }";
        }

        $args = '';
        foreach ($properties as $property) {
            $args .= "            {$property['name']}: {$this->read($property)},\n";
        }
        $construct = $args === '' ? 'new self()' : "new self(\n{$args}        )";
        $methods[] = <<<PHP
                /**
                 * @param array<string, mixed> \$data
                 */
                public static function fromArray(array \$data): self
                {
                    return {$construct};
                }
            PHP;

        $entries = '';
        $optional = '';
        foreach ($fields as $field) {
            $key = \var_export($field['wire'], true);
            $value = "\$this->{$field['name']}";
            if (\array_key_exists('const', $field['type'])) {
                $entries .= "            {$key} => " . \var_export($field['type']['const'], true) . ",\n";
            } elseif ($field['required']) {
                $encoded = $this->encode($field['type'], $value, self::isNullable($field['type']));
                $entries .= "            {$key} => {$encoded},\n";
            } else {
                $optional .= "        if ({$value} !== null) {\n"
                    . "            \$data[{$key}] = {$this->encode($field['type'], $value)};\n"
                    . "        }\n";
            }
        }
        $array = $entries === '' ? '[]' : "[\n{$entries}        ]";
        $body = $optional === '' ? "return {$array};" : "\$data = {$array};\n{$optional}        return \$data;";
        $methods[] = <<<PHP
                /**
                 * @return array<string, mixed>
                 */
                public function toArray(): array
                {
                    {$body}
                }
            PHP;

        $implements = $this->parents($name);
        $declaration = 'final readonly class';
        return $this->file(
            $this->fqcn($name),
            $implements === [] ? $declaration : $declaration . ' %s implements ' . \implode(', ', $implements),
            $methods,
        );
    }

    /**
     * @param Node $node
     * @return array<string, string>
     */
    private function enum(string $name, array $node): array
    {
        $cases = '';
        $values = $node['values'] ?? null;
        foreach (\is_array($values) ? $values : [] as $value) {
            if (!\is_string($value) && !\is_int($value)) {
                throw new RuntimeException("{$name} has a value that is neither a string nor an integer");
            }
            $cases .= '    case ' . self::caseName((string) $value) . ' = ' . \var_export($value, true) . ";\n";
        }
        $backing = self::primitive($node);
        return $this->file($this->fqcn($name), "enum %s: {$backing}", [\rtrim($cases)]);
    }

    /**
     * @param Node $node
     * @return array<string, string>
     */
    private function union(string $name, array $node): array
    {
        $parents = $this->parents($name);
        $interface = $this->file(
            $this->fqcn($name),
            $parents === [] ? 'interface' : 'interface %s extends ' . \implode(', ', $parents),
            ["    /**\n     * @return array<string, mixed>\n     */\n    public function toArray(): array;"],
        );

        $selector = self::node($node['selector'] ?? null);
        $short = self::shortName($name);
        $arms = '';
        if (isset($selector['by'])) {
            $subject = "\$data['" . self::string($selector, 'by') . "'] ?? null";
            foreach (self::nodes($selector, 'variants') as $variant) {
                $arms .= '            ' . \var_export($variant['value'] ?? null, true)
                    . " => {$this->reader(['ref' => $variant['ref'] ?? null])}::fromArray(\$data),\n";
            }
        } else {
            $subject = 'true';
            foreach (self::nodes($selector, 'ordered') as $variant) {
                $keys = \array_map(
                    static fn (string $key): string => "\$data['{$key}']",
                    self::strings($variant, 'requires'),
                );
                $arms .= '            isset(' . \implode(', ', $keys)
                    . ") => {$this->reader(['ref' => $variant['ref'] ?? null])}::fromArray(\$data),\n";
            }
        }
        $default = self::optionalString($selector, 'default');
        $arms .= $default === null
            ? '            default => throw ' . self::WIRE . "::unknown('{$short}', \$data),\n"
            : "            default => {$this->reader(['ref' => $default])}::fromArray(\$data),\n";
        $type = '{{' . $this->fqcn($name) . '}}';
        $factory = $this->file($this->fqcn($name) . 'Factory', 'final class', [<<<PHP
                /**
                 * @param array<string, mixed> \$data
                 */
                public static function fromArray(array \$data): {$type}
                {
                    return match ({$subject}) {
            {$arms}        };
                }
            PHP]);
        return $interface + $factory;
    }

    /**
     * @param Field $property
     */
    private function read(array $property): string
    {
        $key = "\$data[" . \var_export($property['wire'], true) . ']';
        if ($property['required'] && !self::isNullable($property['type'])) {
            return $this->decode($property['type'], "{$key} ?? null", '            ');
        }
        $decoded = $this->decode($property['type'], $key, '                ');
        $read = "isset({$key}) ? {$decoded} : null";
        $line = "            {$property['name']}: {$read},";
        if (!\str_contains($read, "\n") && self::width($line) <= 120) {
            return $read;
        }
        return "isset({$key})\n                ? {$decoded}\n                : null";
    }

    /**
     * Decodes a value of mixed type, breaking what holds a closure or a choice across lines below $indent.
     *
     * @param Node $type
     */
    private function decode(array $type, string $value, string $indent): string
    {
        return match ($this->kind($type)) {
            'record', 'union' => "{$this->reader($type)}::fromArray(" . self::WIRE . "::map({$value}))",
            'enum' => "{$this->phpType($type)}::from({$this->decode($this->backing($type), $value, $indent)})",
            'primitive' => $type['primitive'] === 'any' ? $value : self::narrower($type) . "({$value})",
            'list' => $this->decodeEach('list', $this->resolve(self::node($type['list'])), $value, $indent),
            'map' => $this->decodeEach('dict', $this->resolve(self::node($type['map'])), $value, $indent),
            'choice' => $this->decodeChoice($this->arms($type), $value, $indent),
        };
    }

    /**
     * @param Node $item
     */
    private function decodeEach(string $method, array $item, string $value, string $indent): string
    {
        $kind = $this->kind($item);
        if ($method === 'list' && ($kind === 'record' || $kind === 'union')) {
            return self::WIRE . "::records({$value}, {$this->reader($item)}::fromArray(...))";
        }
        if ($kind === 'primitive' && $item['primitive'] !== 'any') {
            return self::WIRE . "::{$method}({$value}, " . self::narrower($item) . '(...))';
        }
        $inner = "{$indent}    ";
        return self::WIRE . "::{$method}(\n{$inner}{$value},\n"
            . "{$inner}static fn (mixed \$item): {$this->phpType($item)} => {$this->decode($item, '$item', $inner)},\n"
            . "{$indent})";
    }

    /**
     * @param list<Node> $arms
     */
    private function decodeChoice(array $arms, string $value, string $indent): string
    {
        $last = \array_pop($arms);
        if ($last === null) {
            throw new RuntimeException('A union with no arms');
        }
        $inner = "{$indent}    ";
        $code = $this->decode($last, $value, $inner);
        // Past its guard, the value is known to be there.
        $guarded = \substr($value, -8) === ' ?? null' ? \substr($value, 0, -8) : $value;
        foreach (\array_reverse($arms) as $i => $arm) {
            $code = $this->guard($arm, $value) . "\n{$inner}? {$this->decode($arm, $guarded, $inner)}\n{$inner}: "
                . ($i === 0 ? $code : "({$code})");
        }
        return $code;
    }

    /**
     * @param Node $type
     */
    private function guard(array $type, string $value): string
    {
        $kind = $this->kind($type);
        if ($kind === 'enum') {
            return $this->guard($this->backing($type), $value);
        }
        if ($kind !== 'primitive') {
            return "\\is_array({$value})";
        }
        $primitive = self::string($type, 'primitive');
        $guard = self::GUARDS[$primitive] ?? throw new RuntimeException("Cannot tell {$primitive} apart");
        return \sprintf($guard, $value);
    }

    /**
     * Encodes a value for the wire; a record, a union, a list or a choice is left to Wire.
     *
     * @param Node $type
     */
    private function encode(array $type, string $value, bool $nullable = false): string
    {
        return match ($this->kind($type)) {
            'enum' => $nullable ? "{$value}?->value" : "{$value}->value",
            'primitive' => $value,
            'map' => ($nullable ? "{$value} === null ? null : " : '') . self::WIRE . "::encodeMap({$value})",
            default => self::WIRE . "::encode({$value})",
        };
    }

    /**
     * The primitive an enum is backed by.
     *
     * @param Node $type
     * @return Node
     */
    private function backing(array $type): array
    {
        return ['primitive' => self::string($this->type(self::ref($type)), 'primitive')];
    }

    /**
     * @param Node $type
     */
    private function phpType(array $type): string
    {
        return match ($this->kind($type)) {
            'record', 'enum', 'union' => '{{' . $this->fqcn(self::ref($type)) . '}}',
            'primitive' => self::primitive($type),
            'list', 'map' => 'array',
            'choice' => \implode('|', \array_map($this->phpType(...), $this->arms($type))),
        };
    }

    /**
     * @param Node $type
     */
    private function docType(array $type): string
    {
        return match ($this->kind($type)) {
            'list' => 'list<' . $this->docType($this->resolve(self::node($type['list']))) . '>',
            'map' => 'array<string, ' . $this->docType($this->resolve(self::node($type['map']))) . '>',
            'choice' => \implode('|', \array_map($this->docType(...), $this->arms($type))),
            default => $this->phpType($type),
        };
    }

    /**
     * The class that reads a record or a union from an array.
     *
     * @param Node $type
     */
    private function reader(array $type): string
    {
        $ref = self::ref($type);
        return match (self::string($this->type($ref), 'kind')) {
            'record' => '{{' . $this->fqcn($ref) . '}}',
            'union' => '{{' . $this->fqcn($ref) . 'Factory}}',
            default => throw new RuntimeException("{$ref} is neither a record nor a union"),
        };
    }

    /**
     * @param Node $type
     * @return 'record'|'enum'|'union'|'primitive'|'list'|'map'|'choice'
     */
    private function kind(array $type): string
    {
        return match (true) {
            isset($type['ref']) => $this->classKind(self::ref($type)),
            isset($type['primitive']) => 'primitive',
            isset($type['list']) => 'list',
            isset($type['map']) => 'map',
            isset($type['union']) => 'choice',
            default => throw new RuntimeException('Unknown type ' . \json_encode($type)),
        };
    }

    /**
     * @return 'record'|'enum'|'union'
     */
    private function classKind(string $ref): string
    {
        $kind = self::string($this->type($ref), 'kind');
        return \in_array($kind, ['record', 'enum', 'union'], true)
            ? $kind
            : throw new RuntimeException("{$ref} is a {$kind}, which is resolved before it is used");
    }

    /**
     * Follows aliases, and reads a record that is only a map as that map.
     *
     * @param Node $type
     * @return Node
     */
    private function resolve(array $type): array
    {
        if (!isset($type['ref'])) {
            return $type;
        }
        $node = $this->type(self::ref($type));
        $target = match (true) {
            $node['kind'] === 'alias' => self::node($node['type'] ?? null),
            $node['kind'] === 'record' && isset($node['map']) => ['map' => $node['map']],
            default => null,
        };
        if ($target === null) {
            return $type;
        }
        $resolved = $this->resolve($target);
        if (self::isNullable($type)) {
            $resolved['nullable'] = true;
        }
        return $resolved;
    }

    /**
     * @param Node $type
     * @return list<Node>
     */
    private function arms(array $type): array
    {
        return \array_map($this->resolve(...), self::nodes($type, 'union'));
    }

    /**
     * @param Node $field
     * @return Field
     */
    private function field(array $field): array
    {
        $name = self::string($field, 'name');
        return [
            'name' => $name === 'this' ? 'this_' : $name,
            'wire' => self::string($field, 'wire'),
            'required' => ($field['required'] ?? false) === true,
            'type' => $this->resolve(self::node($field['type'] ?? null)),
        ];
    }

    /**
     * The generated unions a type is a variant of.
     *
     * @return list<string>
     */
    private function parents(string $name): array
    {
        $parents = [];
        foreach ($this->reachable as $union) {
            if (\in_array($name, self::strings($this->type($union), 'variants'), true)) {
                $parents[] = '{{' . $this->fqcn($union) . '}}';
            }
        }
        return $parents;
    }

    /**
     * @param list<string> $members
     * @return array<string, string>
     */
    private function file(string $fqcn, string $declaration, array $members): array
    {
        $separator = \strrpos($fqcn, '\\');
        $namespace = \substr($fqcn, 0, (int) $separator);
        $class = \substr($fqcn, (int) $separator + 1);
        $declaration = \str_contains($declaration, '%s') ? \sprintf($declaration, $class) : "{$declaration} {$class}";
        $code = "{$declaration}\n{\n" . \implode("\n\n", $members) . "\n}\n";

        \preg_match_all('/\{\{(.+?)\}\}/', $code, $matches);
        $references = \array_unique($matches[1]);
        \sort($references);
        $names = [];
        $taken = [$class => true];
        foreach ($references as $reference) {
            if (\substr($reference, 0, (int) \strrpos($reference, '\\')) === $namespace) {
                $names[$reference] = self::shortName($reference, '\\');
                $taken[$names[$reference]] = true;
            }
        }
        $imports = '';
        foreach ($references as $reference) {
            if (isset($names[$reference])) {
                continue;
            }
            $short = self::shortName($reference, '\\');
            $segments = \explode('\\', $reference);
            $alias = isset($taken[$short]) ? $segments[\count($segments) - 2] . $short : $short;
            $names[$reference] = $alias;
            $taken[$alias] = true;
            $imports .= "use {$reference}" . ($alias === $short ? '' : " as {$alias}") . ";\n";
        }
        $code = \preg_replace_callback('/\{\{(.+?)\}\}/', static fn (array $match): string => $names[$match[1]], $code);

        $path = \str_replace('\\', '/', \substr($fqcn, \strlen(self::ROOT) + 1)) . '.php';
        $header = "<?php\n\n/**\n * " . self::HEADER . "\n */\n\n"
            . "declare(strict_types=1);\n\nnamespace {$namespace};\n\n";
        return [$path => $header . ($imports === '' ? '' : "{$imports}\n") . $code];
    }

    private function fqcn(string $name): string
    {
        $dot = \strpos($name, '.');
        if ($dot === false) {
            throw new RuntimeException("{$name} belongs to no module");
        }
        return self::ROOT . '\\' . \ucfirst(\substr($name, 0, $dot)) . '\\' . self::shortName($name);
    }

    /**
     * @return Node
     */
    private function type(string $name): array
    {
        return $this->types[$name] ?? throw new RuntimeException("{$name} is not in the schema");
    }

    private static function caseName(string $value): string
    {
        $name = \strtoupper(\str_replace(['-', '.', ' '], '_', $value));
        return \ctype_digit($name[0] ?? '') ? "_{$name}" : $name;
    }

    private static function shortName(string $name, string $separator = '.'): string
    {
        $position = \strrpos($name, $separator);
        return $position === false ? $name : \substr($name, $position + 1);
    }

    /**
     * @param Node $type
     */
    private static function primitive(array $type): string
    {
        $primitive = self::string($type, 'primitive');
        return self::PRIMITIVES[$primitive] ?? throw new RuntimeException("Unknown primitive {$primitive}");
    }

    /**
     * The Wire method that narrows decoded JSON to a primitive.
     *
     * @param Node $type
     */
    private static function narrower(array $type): string
    {
        $primitive = self::string($type, 'primitive');
        $narrower = self::NARROWERS[$primitive] ?? throw new RuntimeException("Cannot read {$primitive}");
        return self::WIRE . "::{$narrower}";
    }

    /**
     * How wide code is once its class references are resolved to short names.
     */
    private static function width(string $code): int
    {
        return \strlen((string) \preg_replace('/\{\{(?:[^}]*\\\\)?([^}\\\\]+)\}\}/', '$1', $code));
    }

    private static function orNull(string $type): string
    {
        return match (true) {
            $type === 'mixed' => $type,
            \str_contains($type, '|') => "{$type}|null",
            default => "?{$type}",
        };
    }

    /**
     * @param Node $type
     */
    private static function isNullable(array $type): bool
    {
        return ($type['nullable'] ?? false) === true;
    }

    /**
     * @param Node $type
     */
    private static function ref(array $type): string
    {
        return self::string($type, 'ref');
    }

    /**
     * @param list<Node> $entries
     * @param list<string> $methods
     * @return list<Node>
     */
    private static function pick(array $entries, array $methods): array
    {
        $picked = \array_values(\array_filter(
            $entries,
            static fn (array $entry): bool => \in_array($entry['method'] ?? null, $methods, true),
        ));
        $missing = \array_diff($methods, \array_column($picked, 'method'));
        if ($missing !== []) {
            throw new RuntimeException('Not in the schema: ' . \implode(', ', $missing));
        }
        return $picked;
    }

    /**
     * @return list<string>
     */
    private static function refs(mixed $node): array
    {
        if (!\is_array($node)) {
            return [];
        }
        $refs = isset($node['ref']) && \is_string($node['ref']) ? [$node['ref']] : [];
        foreach ($node as $value) {
            $refs = [...$refs, ...self::refs($value)];
        }
        return $refs;
    }

    /**
     * @return Node
     */
    private static function node(mixed $value): array
    {
        if (!\is_array($value)) {
            throw new RuntimeException('Expected an object in the schema, got ' . \get_debug_type($value));
        }
        /** @var Node $value */
        return $value;
    }

    /**
     * @param Node $node
     * @return list<Node>
     */
    private static function nodes(array $node, string $key): array
    {
        $values = $node[$key] ?? [];
        return \is_array($values) ? \array_values(\array_map(self::node(...), $values)) : [];
    }

    /**
     * @param Node $node
     */
    private static function string(array $node, string $key): string
    {
        return self::optionalString($node, $key) ?? throw new RuntimeException("Expected a string at {$key}");
    }

    /**
     * @param Node $node
     */
    private static function optionalString(array $node, string $key): ?string
    {
        $value = $node[$key] ?? null;
        return \is_string($value) ? $value : null;
    }

    /**
     * @param Node $node
     * @return list<string>
     */
    private static function strings(array $node, string $key): array
    {
        $values = $node[$key] ?? [];
        return \is_array($values) ? \array_values(\array_filter($values, \is_string(...))) : [];
    }
}
