<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

use Rnscommerce\WebDriver\BiDi\BrowsingContext\GetTreeParameters;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\BrowsingContext;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\Session;
use Rnscommerce\WebDriver\BiDi\Session\SubscribeParameters;
use Rnscommerce\WebDriver\BiDi\Session\UnsubscribeByIDRequest;
use Rnscommerce\WebDriver\BiDi\Transport\AmpWebSocket;

final class BiDi
{
    /** @var array<string, int> each subscription's listener on the connection */
    private array $listeners = [];

    /**
     * @param float $timeout Seconds a command waits for its answer.
     */
    public function __construct(private readonly Connection $connection, private readonly float $timeout)
    {
    }

    /**
     * Connects to the webSocketUrl capability of a WebDriver session started with webSocketUrl: true.
     *
     * @param float $timeout Seconds a command waits for its answer.
     * @param (callable(\Throwable): void)|null $onListenerError called with whatever a listener throws, which
     *     otherwise goes to PHP's error log
     */
    public static function connect(
        string $webSocketUrl,
        float $timeout = 30.0,
        ?callable $onListenerError = null,
    ): self {
        return new self(new Connection(AmpWebSocket::connect($webSocketUrl), $onListenerError), $timeout);
    }

    /**
     * @template X
     * @param Command<X> $command
     * @return X
     */
    public function send(Command $command, ?float $timeout = null): mixed
    {
        return $this->connection->sendAndWait($command, $timeout ?? $this->timeout);
    }

    /**
     * Subscribes to the event, in these browsing contexts or else in all of them, and calls the handler with each
     * one that arrives. Returns the subscription id that removeListener takes.
     *
     * @template X
     * @param Event<X> $event
     * @param callable(X): void $handler
     * @param list<string> $browsingContextIds
     */
    public function addListener(Event $event, callable $handler, array $browsingContextIds = []): string
    {
        $listener = $this->connection->addListener($event, $handler);
        try {
            $subscription = $this->send(Session::subscribe(new SubscribeParameters(
                [$event->getMethod()],
                $browsingContextIds === [] ? null : $browsingContextIds,
            )))->subscription;
        } catch (\Throwable $e) {
            $this->connection->removeListener($listener);
            throw $e;
        }
        $this->listeners[$subscription] = $listener;
        return $subscription;
    }

    /**
     * Stops calling the listener's handler, and unsubscribes from its event.
     */
    public function removeListener(string $id): void
    {
        if (!isset($this->listeners[$id])) {
            return;
        }
        $this->connection->removeListener($this->listeners[$id]);
        unset($this->listeners[$id]);
        $this->send(Session::unsubscribe(new UnsubscribeByIDRequest([$id])));
    }

    /**
     * Removes every listener, unsubscribing from each event where the browser and the connection still allow it.
     */
    public function clearListeners(): void
    {
        foreach (array_keys($this->listeners) as $id) {
            try {
                $this->removeListener((string) $id);
            } catch (BiDiException) {
            }
        }
    }

    /**
     * Delivers every event the browser sent before this call, by waiting for the answer to a command sent after them.
     */
    public function drain(): void
    {
        $this->send(BrowsingContext::getTree(new GetTreeParameters(0)));
    }

    /**
     * Delivers events until the condition holds, checking it after drain() and again after each call to a listener.
     * Returns false if it still does not hold after $timeout seconds.
     *
     * @param callable(): bool $condition
     */
    public function waitFor(callable $condition, float $timeout): bool
    {
        $deadline = microtime(true) + $timeout;
        $this->drain();
        while (!$condition()) {
            $remaining = $deadline - microtime(true);
            if ($remaining <= 0) {
                return false;
            }
            $this->connection->awaitDelivery($remaining);
        }
        return true;
    }

    public function isClosed(): bool
    {
        return $this->connection->isClosed();
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
