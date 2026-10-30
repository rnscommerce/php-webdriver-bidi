<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

/**
 * An error answer from the remote end: its error code, such as "no such frame", and its message.
 */
final class ErrorResponseException extends BiDiException
{
    public function __construct(private readonly string $error, string $message)
    {
        parent::__construct($message);
    }

    public function getError(): string
    {
        return $this->error;
    }
}
