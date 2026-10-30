<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests;

use Revolt\EventLoop;
use Revolt\EventLoop\Suspension;
use Rnscommerce\WebDriver\BiDi\Transport\WebSocket;

/**
 * An in-memory socket: what is sent is recorded and handed to the remote end, and what is pushed is received.
 */
final class FakeWebSocket implements WebSocket
{
    /** @var list<array<string, mixed>> */
    public array $sent = [];

    /** @var list<string> */
    private array $incoming = [];

    private bool $closed = false;

    /** @var Suspension<null>|null */
    private ?Suspension $receiver = null;

    /**
     * @param \Closure(array<string, mixed>, self): void $remote
     */
    public function __construct(private readonly \Closure $remote)
    {
    }

    public function sendText(string $text): void
    {
        /** @var array<string, mixed> $message */
        $message = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        $this->sent[] = $message;
        ($this->remote)($message, $this);
    }

    public function receive(): ?string
    {
        if ($this->incoming === [] && !$this->closed) {
            $this->receiver = EventLoop::getSuspension();
            $this->receiver->suspend();
        }
        return array_shift($this->incoming);
    }

    public function close(): void
    {
        $this->closed = true;
        $this->wake();
    }

    /**
     * @param array<string, mixed> $message
     */
    public function push(array $message): void
    {
        $this->incoming[] = json_encode($message, JSON_THROW_ON_ERROR);
        $this->wake();
    }

    private function wake(): void
    {
        $receiver = $this->receiver;
        $this->receiver = null;
        $receiver?->resume();
    }
}
