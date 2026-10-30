<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

use Amp\DeferredFuture;
use Revolt\EventLoop;
use Rnscommerce\WebDriver\BiDi\Transport\WebSocket;

use function Amp\delay;

/**
 * Sends commands over one WebDriver BiDi socket, matches their answers, and passes events to listeners.
 *
 * A fiber reads the socket whenever the event loop runs: while a command is awaited, during drain(),
 * or under anything else that runs the loop. Called from code outside any fiber, an await runs the loop
 * until its own answer arrives.
 */
final class Connection
{
    private int $nextId = 1;

    private int $nextListenerId = 1;

    private int $received = 0;

    private bool $closed = false;

    private ?\Throwable $listenerFailure = null;

    /** @var array<int, DeferredFuture<array<string, mixed>>> */
    private array $pending = [];

    /** @var array<int, array{string, \Closure(array<string, mixed>): void}> */
    private array $listeners = [];

    public function __construct(private readonly WebSocket $socket)
    {
        EventLoop::queue($this->read(...));
    }

    /**
     * @template X
     * @param Command<X> $command
     * @return X
     */
    public function sendAndWait(Command $command, float $timeout): mixed
    {
        if ($this->closed) {
            throw new ConnectionClosedException('The BiDi connection is closed');
        }
        $id = $this->nextId++;
        /** @var DeferredFuture<array<string, mixed>> $answer */
        $answer = new DeferredFuture();
        $this->pending[$id] = $answer;
        $timer = EventLoop::delay($timeout, function () use ($id, $command, $timeout): void {
            $this->take($id)?->error(new TimeoutException(
                sprintf('%s had no answer within %s seconds', $command->getMethod(), $timeout),
            ));
        });
        try {
            // Cast, because an empty array would encode as a JSON list where BiDi requires an object.
            $this->socket->sendText(json_encode(
                ['id' => $id, 'method' => $command->getMethod(), 'params' => (object) $command->getParams()],
                JSON_THROW_ON_ERROR,
            ));
            $result = $answer->getFuture()->await();
        } finally {
            EventLoop::cancel($timer);
            unset($this->pending[$id]);
        }
        $this->throwListenerFailure();
        return $command->mapResult($result);
    }

    /**
     * @template X
     * @param Event<X> $event
     * @param callable(X): void $handler
     */
    public function addListener(Event $event, callable $handler): int
    {
        $deliver = static function (array $params) use ($event, $handler): void {
            /** @var array<string, mixed> $params */
            $handler($event->mapParams($params));
        };
        $id = $this->nextListenerId++;
        $this->listeners[$id] = [$event->getMethod(), $deliver];
        return $id;
    }

    public function removeListener(int $id): void
    {
        unset($this->listeners[$id]);
    }

    /**
     * Delivers the events that arrived while the event loop was not running.
     */
    public function drain(): void
    {
        do {
            $received = $this->received;
            delay(0);
        } while ($this->received !== $received);
        $this->throwListenerFailure();
    }

    public function close(): void
    {
        if ($this->closed) {
            return;
        }
        $this->fail(new ConnectionClosedException('The BiDi connection was closed'));
        $this->socket->close();
    }

    private function read(): void
    {
        try {
            while (($text = $this->socket->receive()) !== null) {
                $this->received++;
                $this->handle(json_decode($text, true));
            }
            $this->fail(new ConnectionClosedException('The BiDi connection closed'));
        } catch (\Throwable $e) {
            $this->fail(new ConnectionClosedException('The BiDi connection failed: ' . $e->getMessage(), 0, $e));
        }
    }

    private function handle(mixed $message): void
    {
        if (!is_array($message)) {
            return;
        }
        $id = $message['id'] ?? null;
        $params = $message['params'] ?? null;
        if (($message['type'] ?? null) === 'event' && is_string($message['method'] ?? null) && is_array($params)) {
            /** @var array<string, mixed> $params */
            $this->dispatch($message['method'], $params);
        } elseif (is_int($id) && ($answer = $this->take($id)) !== null) {
            $this->settle($answer, $message);
        }
    }

    /**
     * @return DeferredFuture<array<string, mixed>>|null
     */
    private function take(int $id): ?DeferredFuture
    {
        $answer = $this->pending[$id] ?? null;
        unset($this->pending[$id]);
        return $answer;
    }

    /**
     * @param DeferredFuture<array<string, mixed>> $answer
     * @param array<mixed> $message
     */
    private function settle(DeferredFuture $answer, array $message): void
    {
        if (($message['type'] ?? null) === 'error') {
            $error = $message['error'] ?? '';
            $text = $message['message'] ?? '';
            $answer->error(new ErrorResponseException(
                is_string($error) ? $error : '',
                is_string($text) ? $text : '',
            ));
            return;
        }
        /** @var array<string, mixed> $result */
        $result = is_array($message['result'] ?? null) ? $message['result'] : [];
        $answer->complete($result);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function dispatch(string $method, array $params): void
    {
        foreach ($this->listeners as [$listened, $deliver]) {
            if ($listened !== $method) {
                continue;
            }
            EventLoop::queue(function () use ($deliver, $params): void {
                try {
                    $deliver($params);
                } catch (\Throwable $e) {
                    $this->listenerFailure ??= $e;
                }
            });
        }
    }

    private function throwListenerFailure(): void
    {
        $failure = $this->listenerFailure;
        $this->listenerFailure = null;
        if ($failure !== null) {
            throw $failure;
        }
    }

    private function fail(ConnectionClosedException $reason): void
    {
        $this->closed = true;
        $pending = $this->pending;
        $this->pending = [];
        foreach ($pending as $answer) {
            $answer->error($reason);
        }
    }
}
