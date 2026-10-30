<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

/**
 * A WebDriver BiDi event: its method, and how its parameters are read.
 *
 * @template-covariant T
 */
final class Event
{
    /**
     * @param \Closure(array<string, mixed>): T $mapper
     */
    public function __construct(
        private readonly string $method,
        private readonly \Closure $mapper,
    ) {
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param array<string, mixed> $params
     * @return T
     */
    public function mapParams(array $params): mixed
    {
        return ($this->mapper)($params);
    }
}
