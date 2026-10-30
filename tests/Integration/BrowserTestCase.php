<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Rnscommerce\WebDriver\BiDi\BiDi;

/**
 * Starts a driver and a fixture server per test class, and a headless session with a BiDi connection per test.
 */
abstract class BrowserTestCase extends TestCase
{
    private static ?WebDriverProcess $driver = null;

    private static ?WebDriverProcess $server = null;

    protected WebDriverSession $session;

    protected BiDi $bidi;

    protected string $handle;

    /** @var list<BiDi> */
    private array $connections = [];

    /**
     * @return string|null why this browser cannot run here, or null when it can
     */
    abstract protected static function unavailable(): ?string;

    /**
     * @return list<string>
     */
    abstract protected static function driverCommand(int $port): array;

    /**
     * @return array<string, mixed>
     */
    abstract protected static function capabilities(): array;

    public static function setUpBeforeClass(): void
    {
        $reason = static::unavailable();
        if ($reason !== null) {
            self::markTestSkipped($reason);
        }
        self::$server = WebDriverProcess::start(
            static fn (int $port): array => [PHP_BINARY, '-S', "127.0.0.1:$port", '-t', __DIR__ . '/fixtures'],
            '/probe.html',
        );
        self::$driver = WebDriverProcess::start(static::driverCommand(...), '/status');
    }

    public static function tearDownAfterClass(): void
    {
        self::$driver?->stop();
        self::$server?->stop();
        self::$driver = self::$server = null;
    }

    protected function setUp(): void
    {
        $driver = self::$driver ?? throw new \LogicException('No driver');
        $this->session = WebDriverSession::start("http://127.0.0.1:{$driver->port}", static::capabilities());
        $this->handle = $this->session->windowHandle();
        $this->bidi = $this->connect();
    }

    protected function tearDown(): void
    {
        foreach ($this->connections as $bidi) {
            $bidi->close();
        }
        $this->connections = [];
        $this->session->delete();
    }

    protected function connect(): BiDi
    {
        return $this->connections[] = BiDi::connect($this->session->webSocketUrl, 20.0);
    }

    protected function url(string $page): string
    {
        $server = self::$server ?? throw new \LogicException('No server');
        return "http://127.0.0.1:{$server->port}/$page";
    }

    /**
     * @param callable(): bool $condition
     */
    protected function waitFor(callable $condition, string $what, float $seconds = 10.0, ?BiDi $bidi = null): void
    {
        if (!($bidi ?? $this->bidi)->waitFor($condition, $seconds)) {
            self::fail("Gave up waiting for $what");
        }
    }

    protected function settle(float $seconds): void
    {
        $this->bidi->waitFor(static fn (): bool => false, $seconds);
    }

    protected static function freePort(): int
    {
        $socket = stream_socket_server('tcp://127.0.0.1:0');
        self::assertNotFalse($socket);
        $name = (string) stream_socket_get_name($socket, false);
        fclose($socket);
        return (int) substr($name, (int) strrpos($name, ':') + 1);
    }
}
