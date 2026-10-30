<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Transport;

use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\WebsocketClosedException;
use Rnscommerce\WebDriver\BiDi\ConnectionClosedException;

use function Amp\Websocket\Client\connect;

final class AmpWebSocket implements WebSocket
{
    public function __construct(private readonly WebsocketConnection $connection)
    {
    }

    public static function connect(string $url): self
    {
        return new self(connect($url));
    }

    public function sendText(string $text): void
    {
        try {
            $this->connection->sendText($text);
        } catch (WebsocketClosedException $e) {
            throw new ConnectionClosedException('The BiDi connection closed', 0, $e);
        }
    }

    public function receive(): ?string
    {
        return $this->connection->receive()?->buffer();
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
