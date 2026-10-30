<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Transport;

use Amp\Websocket\Client\Rfc6455ConnectionFactory;
use Amp\Websocket\Client\Rfc6455Connector;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use Amp\Websocket\Parser\Rfc6455ParserFactory;
use Amp\Websocket\WebsocketClosedException;
use Rnscommerce\WebDriver\BiDi\ConnectionClosedException;

final class AmpWebSocket implements WebSocket
{
    private const SIZE_LIMIT = 256 * 1024 * 1024;

    public function __construct(private readonly WebsocketConnection $connection)
    {
    }

    public static function connect(string $url): self
    {
        $connector = new Rfc6455Connector(new Rfc6455ConnectionFactory(
            parserFactory: new Rfc6455ParserFactory(
                messageSizeLimit: self::SIZE_LIMIT,
                frameSizeLimit: self::SIZE_LIMIT,
            ),
        ));
        return new self($connector->connect(new WebsocketHandshake($url)));
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
