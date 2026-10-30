<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Integration;

final class ChromeTest extends ProtocolTestCase
{
    protected static function unavailable(): ?string
    {
        $driver = getenv('CHROMEDRIVER');
        return is_string($driver) && is_executable($driver) ? null : 'CHROMEDRIVER does not name a chromedriver';
    }

    protected static function driverCommand(int $port): array
    {
        return [(string) getenv('CHROMEDRIVER'), "--port=$port"];
    }

    protected static function capabilities(): array
    {
        $options = ['args' => ['--headless=new', '--no-sandbox', '--disable-gpu', '--disable-dev-shm-usage']];
        $binary = getenv('CHROME_BINARY');
        if (is_string($binary) && $binary !== '') {
            $options['binary'] = $binary;
        }
        return ['browserName' => 'chrome', 'goog:chromeOptions' => $options];
    }
}
