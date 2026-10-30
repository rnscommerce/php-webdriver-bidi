<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Integration;

/**
 * One classic WebDriver session, over plain curl.
 */
final class WebDriverSession
{
    private bool $deleted = false;

    private function __construct(
        private readonly string $driverUrl,
        public readonly string $id,
        public readonly string $webSocketUrl,
    ) {
    }

    /**
     * @param array<string, mixed> $capabilities
     */
    public static function start(string $driverUrl, array $capabilities): self
    {
        $value = self::request('POST', "$driverUrl/session", [
            'capabilities' => ['alwaysMatch' => $capabilities + ['webSocketUrl' => true]],
        ]);
        if (!is_array($value)) {
            throw new \RuntimeException('No session: ' . json_encode($value));
        }
        $sessionId = $value['sessionId'] ?? null;
        $returned = $value['capabilities'] ?? null;
        $webSocketUrl = is_array($returned) ? ($returned['webSocketUrl'] ?? null) : null;
        if (!is_string($sessionId) || !is_string($webSocketUrl)) {
            throw new \RuntimeException('No session: ' . json_encode($value));
        }
        return new self($driverUrl, $sessionId, $webSocketUrl);
    }

    public function navigate(string $url): void
    {
        $this->command('POST', '/url', ['url' => $url]);
    }

    public function windowHandle(): string
    {
        $handle = $this->command('GET', '/window');
        return is_string($handle) ? $handle : throw new \RuntimeException('No window handle');
    }

    public function switchToWindow(string $handle): void
    {
        $this->command('POST', '/window', ['handle' => $handle]);
    }

    public function delete(): void
    {
        if ($this->deleted) {
            return;
        }
        $this->deleted = true;
        self::request('DELETE', "{$this->driverUrl}/session/{$this->id}");
    }

    /**
     * @param non-empty-string $method
     * @param array<string, mixed>|null $body
     */
    private function command(string $method, string $path, ?array $body = null): mixed
    {
        return self::request($method, "{$this->driverUrl}/session/{$this->id}$path", $body);
    }

    /**
     * @param non-empty-string $method
     * @param array<string, mixed>|null $body
     * @return mixed the answer's value
     */
    private static function request(string $method, string $url, ?array $body = null): mixed
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body ?? (object) [], JSON_THROW_ON_ERROR));
        $answer = curl_exec($curl);
        curl_close($curl);
        $decoded = is_string($answer) ? json_decode($answer, true) : null;
        if (!is_array($decoded) || !array_key_exists('value', $decoded)) {
            throw new \RuntimeException("$method $url: " . var_export($answer, true));
        }
        $value = $decoded['value'];
        if (is_array($value) && isset($value['error'])) {
            throw new \RuntimeException("$method $url: " . json_encode($value));
        }
        return $value;
    }
}
