<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\Log\ConsoleLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\Entry;
use Rnscommerce\WebDriver\BiDi\Log\GenericLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\JavascriptLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\Level;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\Log;

/**
 * Each method subscribes to log entries and returns the id that BiDi::removeListener takes. A level, when given,
 * passes only the entries of that level.
 */
final class LogInspector
{
    /**
     * @param list<string> $browsingContextIds where to listen, or everywhere when empty
     */
    public function __construct(private readonly BiDi $bidi, private readonly array $browsingContextIds = [])
    {
    }

    /**
     * @param callable(ConsoleLogEntry): void $handler
     */
    public function onConsoleEntry(callable $handler, ?Level $level = null): string
    {
        return $this->onEntry(ConsoleLogEntry::class, $handler, $level);
    }

    /**
     * @param callable(JavascriptLogEntry): void $handler
     */
    public function onJavaScriptException(callable $handler): string
    {
        return $this->onEntry(JavascriptLogEntry::class, $handler, Level::ERROR);
    }

    /**
     * @param callable(GenericLogEntry): void $handler
     */
    public function onGenericLog(callable $handler, ?Level $level = null): string
    {
        return $this->onEntry(GenericLogEntry::class, $handler, $level);
    }

    /**
     * @template T of ConsoleLogEntry|JavascriptLogEntry|GenericLogEntry
     * @param class-string<T> $type
     * @param callable(T): void $handler
     */
    private function onEntry(string $type, callable $handler, ?Level $level): string
    {
        $filter = static function (Entry $entry) use ($type, $handler, $level): void {
            if ($entry instanceof $type && ($level === null || $entry->level === $level)) {
                $handler($entry);
            }
        };
        return $this->bidi->addListener(Log::entryAdded(), $filter, $this->browsingContextIds);
    }
}
