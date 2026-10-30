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

use function Amp\async;
use function Amp\Future\await;

final class BiDiTest extends TestCase
{
    private FakeWebSocket $socket;

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
        $bidi->addListener(self::happened(), static function (int $n) use (&$seen): void {
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

    public function testRemovedListenersReceiveNothing(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $seen = [];
        $id = $bidi->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });

        $this->socket->push(self::event('test.happened', ['n' => 1]));
        $bidi->drain();
        $bidi->removeListener($id);
        $this->socket->push(self::event('test.happened', ['n' => 2]));
        $bidi->drain();

        self::assertSame([1], $seen);
    }

    public function testDrainDeliversEventsThatArrivedWhileNothingWasSending(): void
    {
        $bidi = $this->connect(static function (): void {
        });
        $seen = [];
        $bidi->addListener(self::happened(), static function (int $n) use (&$seen): void {
            $seen[] = $n;
        });
        $this->socket->push(self::event('test.happened', ['n' => 1]));
        $this->socket->push(self::event('test.other', ['n' => 2]));
        $this->socket->push(self::event('test.happened', ['n' => 3]));

        self::assertSame([], $seen);
        $bidi->drain();
        self::assertSame([1, 3], $seen);
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
        $bidi->addListener(self::happened(), static function () use ($bidi, &$answers): void {
            $answers[] = $bidi->send(self::command('test.react'));
        });

        $bidi->send(self::command('test.act'));
        $bidi->drain();

        self::assertSame(['test.react'], $answers);
    }

    public function testAFailingListenerThrowsFromTheNextDrainAndTheConnectionCarriesOn(): void
    {
        $bidi = $this->connect(static function (array $message, FakeWebSocket $socket): void {
            $socket->push(self::success($message['id'], ['value' => 'still here']));
        });
        $bidi->addListener(self::happened(), static function (): void {
            throw new \LogicException('Listener failed');
        });
        $this->socket->push(self::event('test.happened', ['n' => 1]));

        try {
            $bidi->drain();
            self::fail('The listener failure was not thrown');
        } catch (\LogicException $e) {
            self::assertSame('Listener failed', $e->getMessage());
        }
        self::assertSame('still here', $bidi->send(self::command('test.act')));
    }

    /**
     * @param \Closure(array<string, mixed>, FakeWebSocket): void $remote
     */
    private function connect(\Closure $remote): BiDi
    {
        $this->socket = new FakeWebSocket($remote);
        return new BiDi(new Connection($this->socket), 1.0);
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
        return new Event('test.happened', static function (array $params): int {
            $n = $params['n'];
            self::assertIsInt($n);
            return $n;
        });
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
