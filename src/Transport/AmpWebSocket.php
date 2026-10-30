<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Transport;

use Amp\Websocket\Client\Rfc6455ConnectionFactory;
use Amp\Websocket\Client\Rfc6455Connector;
use Amp\Websocket\Client\WebsocketConnection;
use Amp\Websocket\Client\WebsocketHandshake;
use Amp\Websocket\Parser\Rfc6455ParserFactory;
use Amp\Websocket\WebsocketClosedException;
use Amp\Websocket\WebsocketCloseCode;
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
            throw $this->closed($e);
        }
    }

    public function receive(): ?string
    {
        $message = $this->connection->receive();
        if ($message !== null) {
            return $message->buffer();
        }
        $info = $this->connection->getCloseInfo();
        if (!$info->isByPeer() && $info->getCode() === WebsocketCloseCode::NORMAL_CLOSE) {
            return null;
        }
        throw $this->closed();
    }

    public function close(): void
    {
        $this->connection->close();
    }

    private function closed(?\Throwable $previous = null): ConnectionClosedException
    {
        $info = $this->connection->getCloseInfo();
        return new ConnectionClosedException(sprintf(
            'The WebSocket was closed by %s with code %d%s',
            $info->isByPeer() ? 'the remote end' : 'this end',
            $info->getCode(),
            $info->getReason() === '' ? '' : ": {$info->getReason()}",
        ), 0, $previous);
    }
}
