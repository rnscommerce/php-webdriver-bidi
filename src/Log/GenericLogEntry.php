<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Log;

use Rnscommerce\WebDriver\BiDi\Script\Source;
use Rnscommerce\WebDriver\BiDi\Script\StackTrace;
use Rnscommerce\WebDriver\BiDi\Wire;

final class GenericLogEntry implements Entry
{
    public function __construct(
        public readonly Level $level,
        public readonly Source $source,
        public readonly ?string $text,
        public readonly int $timestamp,
        public readonly string $type,
        public readonly ?StackTrace $stackTrace = null,
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
            type: Wire::string($data['type'] ?? null),
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
            'type' => $this->type,
        ];
        if ($this->stackTrace !== null) {
            $data['stackTrace'] = Wire::encode($this->stackTrace);
        }
        return $data;
    }
}
