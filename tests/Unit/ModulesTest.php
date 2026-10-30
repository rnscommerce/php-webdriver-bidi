<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\BrowsingContext;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateType;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\NavigationInfo;
use Rnscommerce\WebDriver\BiDi\Connection;
use Rnscommerce\WebDriver\BiDi\Log\ConsoleLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\GenericLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\JavascriptLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\Level;
use Rnscommerce\WebDriver\BiDi\Module\Browser;
use Rnscommerce\WebDriver\BiDi\Module\BrowsingContextInspector;
use Rnscommerce\WebDriver\BiDi\Module\LogInspector;
use Rnscommerce\WebDriver\BiDi\Module\Network;
use Rnscommerce\WebDriver\BiDi\Module\Script;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\DataType;
use Rnscommerce\WebDriver\BiDi\Network\GetDataParameters;
use Rnscommerce\WebDriver\BiDi\Network\StringValue;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\BrowsingContext as BrowsingContextProtocol;
use Rnscommerce\WebDriver\BiDi\Script\ChannelProperties;
use Rnscommerce\WebDriver\BiDi\Script\ChannelValue;
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResultSuccess;
use Rnscommerce\WebDriver\BiDi\Script\MessageParameters;
use Rnscommerce\WebDriver\BiDi\Script\NumberValue;
use Rnscommerce\WebDriver\BiDi\Tests\FakeWebSocket;

final class ModulesTest extends TestCase
{
    private const CONTEXT = 'B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40';

    private FakeWebSocket $socket;

    private BiDi $bidi;

    protected function setUp(): void
    {
        $subscriptions = 0;
        $this->socket = new FakeWebSocket(
            static function (array $message, FakeWebSocket $socket) use (&$subscriptions): void {
                $result = match ($message['method']) {
                    'session.subscribe' => ['subscription' => 'subscription-' . ++$subscriptions],
                    'browser.createUserContext' => ['userContext' => '8E3F1A2B'],
                    'browsingContext.create' => ['context' => '0D7E9A1F', 'userContext' => '8E3F1A2B'],
                    'browsingContext.getTree' => ['contexts' => []],
                    'browsingContext.captureScreenshot' => ['data' => 'iVBORw0KGgo='],
                    'browsingContext.startScreencast' => ['screencast' => 'c3d9', 'path' => '/tmp/c3d9.webm'],
                    'browsingContext.stopScreencast' => ['path' => '/tmp/c3d9.webm'],
                    'script.addPreloadScript' => ['script' => '1f4c2b7e'],
                    'script.callFunction' => [
                        'type' => 'success',
                        'realm' => '6c0f',
                        'result' => ['type' => 'number', 'value' => 2],
                    ],
                    'network.addDataCollector' => ['collector' => '5b6c7d8e'],
                    'network.getData' => ['bytes' => ['type' => 'string', 'value' => '{"success":true}']],
                    default => [],
                };
                $socket->push(['type' => 'success', 'id' => $message['id'], 'result' => $result]);
            },
        );
        $this->bidi = new BiDi(new Connection($this->socket), 1.0);
    }

    public function testAddingAListenerSubscribesAndRemovingItUnsubscribes(): void
    {
        $urls = [];
        $id = $this->bidi->addListener(
            BrowsingContextProtocol::load(),
            static function (NavigationInfo $navigation) use (&$urls): void {
                $urls[] = $navigation->url;
            },
            [self::CONTEXT],
        );
        $this->socket->push(self::event('browsingContext.load', self::navigation('https://magento.test/')));
        $this->bidi->drain();
        $this->bidi->removeListener($id);
        $this->socket->push(self::event('browsingContext.load', self::navigation('https://magento.test/checkout/')));
        $this->bidi->drain();

        self::assertSame('subscription-1', $id);
        self::assertSame(['https://magento.test/'], $urls);
        self::assertSame([
            ['session.subscribe', ['events' => ['browsingContext.load'], 'contexts' => [self::CONTEXT]]],
            ['session.unsubscribe', ['subscriptions' => ['subscription-1']]],
        ], $this->sent());
    }

    public function testInspectorsSubscribeToTheirEventsInTheirContexts(): void
    {
        $ignore = static function (): void {
        };
        $contexts = new BrowsingContextInspector($this->bidi, [self::CONTEXT]);
        $contexts->onBrowsingContextCreated($ignore);
        $contexts->onNavigationStarted($ignore);
        $contexts->onDomContentLoaded($ignore);
        $contexts->onBrowsingContextLoaded($ignore);
        $network = new Network($this->bidi);
        $network->onBeforeRequestSent($ignore);
        $network->onResponseStarted($ignore);
        $network->onResponseCompleted($ignore);
        $network->onFetchError($ignore);
        (new LogInspector($this->bidi))->onConsoleEntry($ignore);
        (new Script($this->bidi))->onMessage($ignore);

        $subscribe = static fn (string $event, array $contexts = []): array => [
            'session.subscribe',
            $contexts === [] ? ['events' => [$event]] : ['events' => [$event], 'contexts' => $contexts],
        ];
        self::assertSame([
            $subscribe('browsingContext.contextCreated', [self::CONTEXT]),
            $subscribe('browsingContext.navigationStarted', [self::CONTEXT]),
            $subscribe('browsingContext.domContentLoaded', [self::CONTEXT]),
            $subscribe('browsingContext.load', [self::CONTEXT]),
            $subscribe('network.beforeRequestSent'),
            $subscribe('network.responseStarted'),
            $subscribe('network.responseCompleted'),
            $subscribe('network.fetchError'),
            $subscribe('log.entryAdded'),
            $subscribe('script.message'),
        ], $this->sent());
    }

    public function testABrowsingContextsCommandsTargetItsId(): void
    {
        $context = new BrowsingContext($this->bidi, self::CONTEXT);

        self::assertSame(self::CONTEXT, $context->getId());
        self::assertSame([], $context->getTree(1));
        self::assertSame('iVBORw0KGgo=', $context->captureScreenshot());
        $screencast = $context->startScreencast();
        $context->stopScreencast($screencast->screencast);
        $context->close();

        self::assertSame([
            ['browsingContext.getTree', ['maxDepth' => 1, 'root' => self::CONTEXT]],
            ['browsingContext.captureScreenshot', ['context' => self::CONTEXT]],
            ['browsingContext.startScreencast', ['context' => self::CONTEXT]],
            ['browsingContext.stopScreencast', ['screencast' => 'c3d9']],
            ['browsingContext.close', ['context' => self::CONTEXT]],
        ], $this->sent());
    }

    public function testATabCreatedInAUserContext(): void
    {
        $browser = new Browser($this->bidi);

        $userContext = $browser->createUserContext();
        $tab = BrowsingContext::create($this->bidi, new CreateParameters(CreateType::TAB, userContext: $userContext));
        $tab->close();
        $browser->removeUserContext($userContext);

        self::assertSame('0D7E9A1F', $tab->getId());
        self::assertSame([
            ['browser.createUserContext', []],
            ['browsingContext.create', ['type' => 'tab', 'userContext' => '8E3F1A2B']],
            ['browsingContext.close', ['context' => '0D7E9A1F']],
            ['browser.removeUserContext', ['userContext' => '8E3F1A2B']],
        ], $this->sent());
    }

    public function testLogInspectorPassesOnlyEntriesOfItsTypeAndLevel(): void
    {
        $inspector = new LogInspector($this->bidi);
        $warnings = [];
        $exceptions = [];
        $generic = [];
        $inspector->onConsoleEntry(static function (ConsoleLogEntry $entry) use (&$warnings): void {
            $warnings[] = $entry->text;
        }, Level::WARN);
        $inspector->onJavaScriptException(static function (JavascriptLogEntry $entry) use (&$exceptions): void {
            $exceptions[] = $entry->text;
        });
        $inspector->onGenericLog(static function (GenericLogEntry $entry) use (&$generic): void {
            $generic[] = $entry->text;
        });

        foreach (
            [
                ['console', 'warn', 'Fallback to JQueryUI Compat activated.'],
                ['console', 'info', 'Customer data loaded'],
                ['javascript', 'error', 'Uncaught TypeError: x is undefined'],
                ['javascript', 'info', 'Not an exception'],
                ['worker', 'debug', 'Worker started'],
            ] as [$type, $level, $text]
        ) {
            $this->socket->push(self::event('log.entryAdded', self::logEntry($type, $level, $text)));
        }
        $this->bidi->drain();

        self::assertSame(['Fallback to JQueryUI Compat activated.'], $warnings);
        self::assertSame(['Uncaught TypeError: x is undefined'], $exceptions);
        self::assertSame(['Worker started'], $generic);
    }

    public function testMessagesThroughAPreloadScriptsChannelReachOnMessage(): void
    {
        $script = new Script($this->bidi);
        $observer = '(send) => new MutationObserver(() => send(1)).observe(document, {subtree: true, childList: true})';

        $id = $script->addPreloadScript($observer, [new ChannelValue(new ChannelProperties('studio-dom'))]);
        $messages = [];
        $script->onMessage(static function (MessageParameters $message) use (&$messages): void {
            $messages[] = $message;
        });
        $this->socket->push(self::event('script.message', [
            'channel' => 'studio-dom',
            'data' => ['type' => 'number', 'value' => 1],
            'source' => ['realm' => '6c0f', 'context' => self::CONTEXT],
        ]));
        $this->bidi->drain();

        self::assertSame('1f4c2b7e', $id);
        self::assertSame(
            ['script.addPreloadScript', [
                'functionDeclaration' => $observer,
                'arguments' => [['type' => 'channel', 'value' => ['channel' => 'studio-dom']]],
            ]],
            $this->sent()[0],
        );
        self::assertCount(1, $messages);
        self::assertSame('studio-dom', $messages[0]->channel);
        self::assertSame(self::CONTEXT, $messages[0]->source->context);
    }

    public function testCallingAFunctionThatAwaitsAPromiseReturnsATypedNumber(): void
    {
        $result = (new Script($this->bidi))->callFunctionInBrowsingContext(
            self::CONTEXT,
            '() => Promise.all(document.getAnimations().map((a) => a.finished)).then((all) => all.length)',
            true,
        );

        self::assertInstanceOf(EvaluationResultSuccess::class, $result);
        self::assertInstanceOf(NumberValue::class, $result->result);
        self::assertSame(2, $result->result->value);
        self::assertSame(
            ['script.callFunction', [
                'functionDeclaration' => '() => Promise.all(document.getAnimations().map((a) => a.finished))'
                    . '.then((all) => all.length)',
                'awaitPromise' => true,
                'target' => ['context' => self::CONTEXT],
            ]],
            $this->sent()[0],
        );
    }

    public function testADataCollectorKeepsResponseBodiesForGetData(): void
    {
        $network = new Network($this->bidi);

        $collector = $network->addDataCollector(new AddDataCollectorParameters([DataType::RESPONSE], 10000000));
        $body = $network->getData(new GetDataParameters(DataType::RESPONSE, '13716.42', $collector));
        $network->removeDataCollector($collector);

        self::assertInstanceOf(StringValue::class, $body);
        self::assertSame('{"success":true}', $body->value);
        self::assertSame([
            ['network.addDataCollector', ['dataTypes' => ['response'], 'maxEncodedDataSize' => 10000000]],
            ['network.getData', ['dataType' => 'response', 'request' => '13716.42', 'collector' => '5b6c7d8e']],
            ['network.removeDataCollector', ['collector' => '5b6c7d8e']],
        ], $this->sent());
    }

    /**
     * @return list<array{mixed, mixed}>
     */
    private function sent(): array
    {
        return \array_map(
            static fn (array $message): array => [$message['method'], $message['params']],
            $this->socket->sent,
        );
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, mixed>
     */
    private static function event(string $method, array $params): array
    {
        return ['type' => 'event', 'method' => $method, 'params' => $params];
    }

    /**
     * @return array<string, mixed>
     */
    private static function navigation(string $url): array
    {
        return [
            'context' => self::CONTEXT,
            'navigation' => '4c5b1e0d',
            'timestamp' => 1761850801502,
            'url' => $url,
            'userContext' => 'default',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function logEntry(string $type, string $level, string $text): array
    {
        $entry = [
            'type' => $type,
            'level' => $level,
            'text' => $text,
            'timestamp' => 1761850801733,
            'source' => ['realm' => '6c0f', 'context' => self::CONTEXT],
        ];
        return $type === 'console' ? $entry + ['method' => $level, 'args' => []] : $entry;
    }
}
