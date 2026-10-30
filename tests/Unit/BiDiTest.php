<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Revolt\EventLoop;
use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\Command;
use Rnscommerce\WebDriver\BiDi\Connection;
use Rnscommerce\WebDriver\BiDi\ConnectionClosedException;
use Rnscommerce\WebDriver\BiDi\ErrorResponseException;
use Rnscommerce\WebDriver\BiDi\Event;
use Rnscommerce\WebDriver\BiDi\Tests\FakeWebSocket;
use Rnscommerce\WebDriver\BiDi\TimeoutException;
use Rnscommerce\WebDriver\BiDi\Wire;

use function Amp\async;
use function Amp\Future\await;

final class BiDiTest extends TestCase
{
    private FakeWebSocket $socket;

    private Connection $connection;

    public function testIdsIncreaseAndEachAnswerResolvesItsCommand(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            $socket->push(self::success($message['id'], ['value' => $message['params']]));
        });

        self::assertSame(['n' => 1], $bidi->send(self::command('test.echo', ['n' => 1])));
        self::assertSame(['n' => 2], $bidi->send(self::command('test.echo', ['n' => 2])));
        self::assertSame([1, 2], array_column($this->socket->sent, 'id'));
    }

    public function testAnswersOutOfOrderResolveTheirOwnCommands(): void
    {
        $held = [];
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket) use (&$held): void {
            $held[] = $message;
            if (count($held) === 2) {
                foreach (array_reverse($held) as $command) {
                    $socket->push(self::success($command['id'], ['value' => $command['method']]));
                }
            }
        });

        $results = await([
            async(static fn (): mixed => $bidi->send(self::command('test.first'))),
            async(static fn (): mixed => $bidi->send(self::command('test.second'))),
        ]);

        self::assertSame([1 => 'test.second', 0 => 'test.first'], $results);
    }

    public function testEventsBeforeTheAnswerReachTheirListenersBeforeSendReturns(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            $socket->push(self::event('test.happened', ['n' => 1]));
            $socket->push(self::event('test.happened', ['n' => 2]));
            $socket->push(self::success($message['id'], ['value' => 'done']));
        });
        $seen = [];
        $this->connection->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });

        $bidi->send(self::command('test.act'));

        self::assertSame([1, 2], $seen);
    }

    public function testAnErrorAnswerRaisesItsCodeAndMessage(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            $socket->push([
                'type' => 'error',
                'id' => $message['id'],
                'error' => 'no such frame',
                'message' => 'Frame abc not found',
            ]);
        });

        try {
            $bidi->send(self::command('browsingContext.navigate'));
            self::fail('No error was raised');
        } catch (ErrorResponseException $e) {
            self::assertSame('no such frame', $e->getError());
            self::assertSame('Frame abc not found', $e->getMessage());
        }
    }

    public function testAnUnansweredCommandTimesOutAndItsLateAnswerIsIgnored(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            if ($message['method'] === 'test.answered') {
                $socket->push(self::success($message['id'], ['value' => 'answered']));
            }
        });

        try {
            $bidi->send(self::command('test.ignored'), 0.01);
            self::fail('No timeout was raised');
        } catch (TimeoutException $e) {
            self::assertSame('test.ignored had no answer within 0.01 seconds', $e->getMessage());
        }
        $this->socket->push(self::success(1, ['value' => 'late']));
        $bidi->drain();

        self::assertSame('answered', $bidi->send(self::command('test.answered')));
    }

    public function testTheSocketClosingFailsThePendingCommand(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            $socket->close();
        });

        $this->expectException(ConnectionClosedException::class);
        $bidi->send(self::command('test.act'));
    }

    public function testClosingFailsThePendingCommandAndRefusesLaterOnes(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        EventLoop::delay(0.01, $bidi->close(...));

        try {
            $bidi->send(self::command('test.act'));
            self::fail('The pending command did not fail');
        } catch (ConnectionClosedException) {
        }

        $this->expectException(ConnectionClosedException::class);
        $bidi->send(self::command('test.act'));
    }

    public function testOnceTheSocketFailsEveryUseThrowsWhyAndSaysItIsClosed(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $cause = new ConnectionClosedException('Connection reset by peer');
        $this->socket->close($cause);

        try {
            $bidi->send(self::command('test.act'));
            self::fail('send() did not throw');
        } catch (ConnectionClosedException $closed) {
            self::assertSame($cause, $closed->getPrevious());
        }
        try {
            $bidi->drain();
            self::fail('drain() did not throw');
        } catch (ConnectionClosedException $e) {
            self::assertSame($closed, $e);
        }
        self::assertTrue($bidi->isClosed());
    }

    public function testAFailureWhileWaitingIsWhatLaterUsesThrow(): void
    {
        $cause = new ConnectionClosedException('Connection reset by peer');
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket) use ($cause): void {
            $socket->close($cause);
        });

        try {
            $bidi->send(self::command('test.act'));
            self::fail('send() did not throw');
        } catch (ConnectionClosedException $closed) {
            self::assertSame($cause, $closed->getPrevious());
        }
        try {
            $bidi->send(self::command('test.act'));
            self::fail('The second send() did not throw');
        } catch (ConnectionClosedException $e) {
            self::assertSame($closed, $e);
        }
    }

    public function testRemovedListenersReceiveNothing(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $seen = [];
        $id = $this->connection->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });

        $this->socket->push(self::event('test.happened', ['n' => 1]));
        $bidi->drain();
        $this->connection->removeListener($id);
        $this->socket->push(self::event('test.happened', ['n' => 2]));
        $bidi->drain();

        self::assertSame([1], $seen);
    }

    public function testAListenerRemovedBeforeItsQueuedEventIsDeliveredReceivesNothing(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            $socket->push(self::success($message['id'], ['value' => 'done']));
            $socket->push(self::event('test.happened', ['n' => 1]));
        });
        $seen = [];
        $id = $this->connection->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });

        $bidi->send(self::command('test.act'));
        $this->connection->removeListener($id);
        $bidi->drain();

        self::assertSame([], $seen);
    }

    public function testDrainDeliversEventsThatArrivedWhileNothingWasSending(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $seen = [];
        $this->connection->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });
        $this->socket->push(self::event('test.happened', ['n' => 1]));
        $this->socket->push(self::event('test.other', ['n' => 2]));
        $this->socket->push(self::event('test.happened', ['n' => 3]));

        self::assertSame([], $seen);
        $bidi->drain();
        self::assertSame([1, 3], $seen);
    }

    public function testWaitForReturnsOnceAnEventArrivingLaterMakesTheConditionHold(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $seen = [];
        $this->connection->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });
        EventLoop::delay(0.05, fn () => $this->socket->push(self::event('test.happened', ['n' => 1])));

        $started = microtime(true);
        self::assertTrue($bidi->waitFor(static function () use (&$seen): bool {
            return $seen !== [];
        }, 1.0));
        self::assertLessThan(0.5, microtime(true) - $started);
    }

    public function testWaitForReturnsFalseWhenTheConditionStillDoesNotHoldAfterTheTimeout(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $this->connection->addListener(self::happened(), static function (): void {
        });
        EventLoop::delay(0.02, fn () => $this->socket->push(self::event('test.happened', ['n' => 1])));

        $started = microtime(true);
        self::assertFalse($bidi->waitFor(static fn (): bool => false, 0.1));
        self::assertGreaterThanOrEqual(0.1, microtime(true) - $started);
    }

    public function testWaitForThrowsWhyTheConnectionClosedWhileItWaited(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $cause = new ConnectionClosedException('Connection reset by peer');
        EventLoop::delay(0.02, fn () => $this->socket->close($cause));

        try {
            $bidi->waitFor(static fn (): bool => false, 1.0);
            self::fail('waitFor() did not throw');
        } catch (ConnectionClosedException $e) {
            self::assertSame($cause, $e->getPrevious());
        }
    }

    public function testDrainDeliversEventsThatArriveAfterItIsCalledAndBeforeItsAnswer(): void
    {
        $socket = new FakeWebSocket(static function (array $message, FakeWebSocket $socket): void {
            EventLoop::delay(0.02, static function () use ($message, $socket): void {
                $socket->push(self::event('test.happened', ['n' => 1]));
                $socket->push(self::success($message['id'], ['contexts' => []]));
            });
        });
        $connection = new Connection($socket);
        $seen = [];
        $connection->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });

        (new BiDi($connection, 1.0))->drain();

        self::assertSame([1], $seen);
        self::assertSame(['browsingContext.getTree'], array_column($socket->sent, 'method'));
        self::assertSame([['maxDepth' => 0]], array_column($socket->sent, 'params'));
    }

    public function testSendBlocksSynchronousCodeUntilTheAnswerArrives(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            EventLoop::delay(0.02, static function () use ($message, $socket): void {
                $socket->push(self::success($message['id'], ['value' => 'later']));
            });
        });

        self::assertNull(\Fiber::getCurrent());
        self::assertSame('later', $bidi->send(self::command('test.act')));
    }

    public function testAListenerCanSendACommand(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            if ($message['method'] === 'test.act') {
                $socket->push(self::event('test.happened', ['n' => 1]));
            }
            $socket->push(self::success($message['id'], ['value' => $message['method']]));
        });
        $answers = [];
        $this->connection->addListener(self::happened(), static function () use ($bidi, &$answers): void {
            $answers[] = $bidi->send(self::command('test.react'));
        });

        $bidi->send(self::command('test.act'));

        self::assertTrue($bidi->waitFor(static function () use (&$answers): bool {
            return $answers !== [];
        }, 1.0));
        self::assertSame(['test.react'], $answers);
    }

    public function testEveryListenerFailureReachesTheErrorHandlerAndTheCommandKeepsItsResult(): void
    {
        $errors = [];
        $bidi = $this->connect(
            static function (array $message, FakeWebSocket $socket): void {
                $socket->push(self::event('test.happened', ['n' => 1]));
                $socket->push(self::event('test.happened', ['n' => 'not a number']));
                $socket->push(self::success($message['id'], ['value' => 'created']));
            },
            static function (\Throwable $e) use (&$errors): void {
                $errors[] = $e->getMessage();
            },
        );
        $this->connection->addListener(self::happened(), static function (): void {
            throw new \LogicException('Listener failed');
        });

        self::assertSame('created', $bidi->send(self::command('test.create')));
        self::assertCount(2, $errors);
        self::assertSame('Listener failed', $errors[0]);
        self::assertStringContainsString('an integer', $errors[1]);
    }

    public function testAddingAListenerReturnsItsSubscriptionWhileAnotherListenerFails(): void
    {
        $errors = 0;
        $bidi = $this->connect(
            static function (array $message, FakeWebSocket $socket): void {
                $socket->push(self::event('test.happened', ['n' => 1]));
                $socket->push(self::success($message['id'], ['subscription' => 'subscription-1']));
            },
            static function () use (&$errors): void {
                $errors++;
            },
        );
        $this->connection->addListener(self::happened(), static function (): void {
            throw new \LogicException('Listener failed');
        });

        $id = $bidi->addListener(new Event('test.other', static fn (): null => null), static function (): void {
        });

        self::assertSame('subscription-1', $id);
        self::assertSame(1, $errors);
        self::assertSame(['session.subscribe'], array_column($this->socket->sent, 'method'));
    }

    public function testAListenerFailureGoesToTheErrorLogWhenNoHandlerIsGiven(): void
    {
        $log = (string) tempnam(sys_get_temp_dir(), 'bidi');
        $previous = ini_set('error_log', $log);
        try {
            $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
                $socket->push(self::event('test.happened', ['n' => 1]));
                $socket->push(self::success($message['id'], ['value' => 'done']));
            });
            $this->connection->addListener(self::happened(), static function (): void {
                throw new \LogicException('Listener failed');
            });

            self::assertSame('done', $bidi->send(self::command('test.act')));
            self::assertStringContainsString('LogicException: Listener failed', (string) file_get_contents($log));
        } finally {
            ini_set('error_log', (string) $previous);
            unlink($log);
        }
    }

    /**
     * @param \Closure(array<string, mixed>, FakeWebSocket): void $remote
     * @param (callable(\Throwable): void)|null $onListenerError
     */
    private function connect(\Closure $remote, ?callable $onListenerError = null): BiDi
    {
        $this->socket = new FakeWebSocket(static function (array $message, FakeWebSocket $socket) use ($remote): void {
            if ($message['method'] === 'browsingContext.getTree') {
                $socket->push(self::success($message['id'], ['contexts' => []]));
                return;
            }
            $remote($message, $socket);
        });
        $this->connection = new Connection($this->socket, $onListenerError);
        return new BiDi($this->connection, 1.0);
    }

    /**
     * @param array<string, mixed> $params
     * @return Command<mixed>
     */
    private static function command(string $method, array $params = []): Command
    {
        return new Command($method, $params, static fn (array $result): mixed => $result['value']);
    }

    /**
     * @return Event<int>
     */
    private static function happened(): Event
    {
        return new Event('test.happened', static fn (array $params): int => Wire::int($params['n'] ?? null));
    }

    /**
     * @param array<string, mixed> $result
     * @return array<string, mixed>
     */
    private static function success(mixed $id, array $result): array
    {
        return ['type' => 'success', 'id' => $id, 'result' => $result];
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function event(string $method, array $params): array
    {
        return ['type' => 'event', 'method' => $method, 'params' => $params];
    }
}
