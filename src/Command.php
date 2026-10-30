<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

/**
 * A WebDriver BiDi command: the method and parameters sent, and how its result is read.
 *
 * @template-covariant T
 */
final class Command
{
    /**
     * @param array<string, mixed> $params
     * @param \Closure(array<string, mixed>): T $mapper
     */
    public function __construct(
        private readonly string $method,
        private readonly array $params,
        private readonly \Closure $mapper,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }

    /**
     * @param array<string, mixed> $result
     * @return T
     */
    public function mapResult(array $result): mixed
    {
        return ($this->mapper)($result);
    }
}
