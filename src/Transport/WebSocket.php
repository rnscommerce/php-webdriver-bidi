<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Transport;

use Rnscommerce\WebDriver\BiDi\ConnectionClosedException;

/**
 * A text WebSocket whose receive suspends the calling fiber, letting the event loop run, until a message arrives.
 */
interface WebSocket
{
    /**
     * @throws ConnectionClosedException
     */
    public function sendText(string $text): void;

    /**
     * The next text message, or null once this end has closed the socket.
     *
     * @throws ConnectionClosedException when the remote end closed the socket, or it failed
     */
    public function receive(): ?string;

    public function close(): void;
}
