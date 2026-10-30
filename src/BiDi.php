<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi;

final class BiDi
{
    /**
     * @param float $timeout Seconds a command waits for its answer.
     */
    public function __construct(private readonly Connection $connection, private readonly float $timeout)
    {
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
     * Calls the handler with each of these events that arrives. Subscribing to them is the session.subscribe command.
     *
     * @template X
     * @param Event<X> $event
     * @param callable(X): void $handler
     */
    public function addListener(Event $event, callable $handler): int
    {
        return $this->connection->addListener($event, $handler);
    }

    public function removeListener(int $id): void
    {
        $this->connection->removeListener($id);
    }

    public function drain(): void
    {
        $this->connection->drain();
    }

    public function close(): void
    {
        $this->connection->close();
    }
}
