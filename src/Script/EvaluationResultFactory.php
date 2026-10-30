<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

use Rnscommerce\WebDriver\BiDi\Wire;

final class EvaluationResultFactory
{
    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): EvaluationResult
    {
        return match ($data['type'] ?? null) {
            'success' => EvaluationResultSuccess::fromArray($data),
            'exception' => EvaluationResultException::fromArray($data),
            default => throw Wire::unknown('EvaluationResult', $data),
        };
    }
}
