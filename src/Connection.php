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

    private ?ConnectionClosedException $closedBy = null;

    /** @var \Closure(\Throwable): void */
    private readonly \Closure $onListenerError;

    /** @var array<int, DeferredFuture<array<string, mixed>>> */
    private array $pending = [];

    /** @var array<int, array{string, \Closure(array<string, mixed>): void}> */
    private array $listeners = [];

    /** @var array<int, DeferredFuture<null>> */
    private array $awaitingDelivery = [];

    /**
     * @param (callable(\Throwable): void)|null $onListenerError called with whatever a listener throws, which
     *     otherwise goes to PHP's error log
     */
    public function __construct(private readonly WebSocket $socket, ?callable $onListenerError = null)
    {
        $this->onListenerError = $onListenerError === null
            ? static function (\Throwable $e): void {
                error_log('A WebDriver BiDi listener failed: ' . $e);
            }
            : $onListenerError(...);
        EventLoop::queue($this->read(...));
    }

    /**
     * @template X
     * @param Command<X> $command
     * @return X
     */
    public function sendAndWait(Command $command, float $timeout): mixed
    {
        $this->throwIfClosed();
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
            try {
                // Cast, because an empty array would encode as a JSON list where BiDi requires an object.
                $this->socket->sendText(json_encode(
                    ['id' => $id, 'method' => $command->getMethod(), 'params' => (object) $command->getParams()],
                    JSON_THROW_ON_ERROR,
                ));
            } catch (ConnectionClosedException $e) {
                $this->failWith($e);
            }
            $result = $answer->getFuture()->await();
        } finally {
            EventLoop::cancel($timer);
            unset($this->pending[$id]);
        }
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
            $this->throwIfClosed();
            $received = $this->received;
            delay(0);
        } while ($this->received !== $received);
        $this->throwIfClosed();
    }

    /**
     * Runs the event loop until a listener has been called, or $timeout seconds pass.
     *
     * @throws ConnectionClosedException
     */
    public function awaitDelivery(float $timeout): void
    {
        $this->throwIfClosed();
        /** @var DeferredFuture<null> $delivered */
        $delivered = new DeferredFuture();
        $key = spl_object_id($delivered);
        $this->awaitingDelivery[$key] = $delivered;
        $timer = EventLoop::delay($timeout, static function () use ($delivered): void {
            if (!$delivered->isComplete()) {
                $delivered->complete();
            }
        });
        try {
            $delivered->getFuture()->await();
        } finally {
            EventLoop::cancel($timer);
            unset($this->awaitingDelivery[$key]);
        }
    }

    public function isClosed(): bool
    {
        return $this->closedBy !== null;
    }

    public function close(): void
    {
        if ($this->isClosed()) {
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
            $this->failWith($e);
        }
    }

    private function failWith(\Throwable $cause): void
    {
        $this->fail(new ConnectionClosedException('The BiDi connection failed: ' . $cause->getMessage(), 0, $cause));
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
        foreach ($this->listeners as $id => [$listened]) {
            if ($listened === $method) {
                EventLoop::queue($this->deliver(...), $id, $params);
            }
        }
    }

    /**
     * @param array<string, mixed> $params
     */
    private function deliver(int $id, array $params): void
    {
        $listener = $this->listeners[$id] ?? null;
        if ($listener === null) {
            return;
        }
        try {
            $listener[1]($params);
        } catch (\Throwable $e) {
            ($this->onListenerError)($e);
        }
        $awaiting = $this->awaitingDelivery;
        $this->awaitingDelivery = [];
        foreach ($awaiting as $delivered) {
            $delivered->complete();
        }
    }

    /**
     * @throws ConnectionClosedException why the connection closed
     */
    private function throwIfClosed(): void
    {
        if ($this->closedBy !== null) {
            throw $this->closedBy;
        }
    }

    private function fail(ConnectionClosedException $reason): void
    {
        if ($this->closedBy !== null) {
            return;
        }
        $this->closedBy = $reason;
        $waiting = [...$this->pending, ...$this->awaitingDelivery];
        $this->pending = $this->awaitingDelivery = [];
        foreach ($waiting as $future) {
            $future->error($reason);
        }
    }
}
