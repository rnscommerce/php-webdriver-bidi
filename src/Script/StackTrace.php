<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final readonly class StackTrace
{
    /**
     * @param list<StackFrame> $callFrames
     */
    public function __construct(
        public array $callFrames,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            callFrames: Wire::records($data['callFrames'] ?? null, StackFrame::fromArray(...)),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'callFrames' => Wire::encode($this->callFrames),
        ];
    }
}
