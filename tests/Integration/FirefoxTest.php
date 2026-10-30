<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Integration;

final class FirefoxTest extends ProtocolTestCase
{
    protected static function unavailable(): ?string
    {
        $driver = getenv('GECKODRIVER');
        return is_string($driver) && is_executable($driver) ? null : 'GECKODRIVER does not name a geckodriver';
    }

    protected static function driverCommand(int $port): array
    {
        $webSocketPort = (string) self::freePort();
        return [(string) getenv('GECKODRIVER'), '--port', (string) $port, '--websocket-port', $webSocketPort];
    }

    protected static function capabilities(): array
    {
        $options = ['args' => ['-headless']];
        $binary = getenv('FIREFOX_BINARY');
        if (is_string($binary) && $binary !== '') {
            $options['binary'] = $binary;
        }
        return ['browserName' => 'firefox', 'moz:firefoxOptions' => $options];
    }
}
