<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Log;

use Rnscommerce\WebDriver\BiDi\Script\RemoteValue;
use Rnscommerce\WebDriver\BiDi\Script\RemoteValueFactory;
use Rnscommerce\WebDriver\BiDi\Script\Source;
use Rnscommerce\WebDriver\BiDi\Script\StackTrace;
use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class ConsoleLogEntry implements Entry
{
    /**
     * @param list<RemoteValue> $args
     */
    public function __construct(
        public Level $level,
        public Source $source,
        public ?string $text,
        public int $timestamp,
        public string $method,
        public array $args,
        public ?StackTrace $stackTrace = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            level: Level::from(Wire::string($data['level'] ?? null)),
            source: Source::fromArray(Wire::map($data['source'] ?? null)),
            text: isset($data['text']) ? Wire::string($data['text']) : null,
            timestamp: Wire::int($data['timestamp'] ?? null),
            method: Wire::string($data['method'] ?? null),
            args: Wire::records($data['args'] ?? null, RemoteValueFactory::fromArray(...)),
            stackTrace: isset($data['stackTrace']) ? StackTrace::fromArray(Wire::map($data['stackTrace'])) : null,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'level' => $this->level->value,
            'source' => Wire::encode($this->source),
            'text' => $this->text,
            'timestamp' => $this->timestamp,
            'type' => 'console',
            'method' => $this->method,
            'args' => Wire::encode($this->args),
        ];
        if ($this->stackTrace !== null) {
            $data['stackTrace'] = Wire::encode($this->stackTrace);
        }
        return $data;
    }
}
