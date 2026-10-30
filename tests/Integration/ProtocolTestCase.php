<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Integration;

use Rnscommerce\WebDriver\BiDi\BrowsingContext\BrowsingContext;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\ContextCreatedParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateType;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\NavigationInfo;
use Rnscommerce\WebDriver\BiDi\ConnectionClosedException;
use Rnscommerce\WebDriver\BiDi\ErrorResponseException;
use Rnscommerce\WebDriver\BiDi\Log\ConsoleLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\JavascriptLogEntry;
use Rnscommerce\WebDriver\BiDi\Module\Browser;
use Rnscommerce\WebDriver\BiDi\Module\BrowsingContextInspector;
use Rnscommerce\WebDriver\BiDi\Module\LogInspector;
use Rnscommerce\WebDriver\BiDi\Module\Network;
use Rnscommerce\WebDriver\BiDi\Module\Script;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\Base64Value;
use Rnscommerce\WebDriver\BiDi\Network\BeforeRequestSentParameters;
use Rnscommerce\WebDriver\BiDi\Network\CollectorType;
use Rnscommerce\WebDriver\BiDi\Network\DataType;
use Rnscommerce\WebDriver\BiDi\Network\FetchErrorParameters;
use Rnscommerce\WebDriver\BiDi\Network\GetDataParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseCompletedParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseStartedParameters;
use Rnscommerce\WebDriver\BiDi\Network\StringValue as NetworkString;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\Session;
use Rnscommerce\WebDriver\BiDi\Script\ChannelProperties;
use Rnscommerce\WebDriver\BiDi\Script\ChannelValue;
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResultSuccess;
use Rnscommerce\WebDriver\BiDi\Script\MessageParameters;
use Rnscommerce\WebDriver\BiDi\Script\NumberValue;
use Rnscommerce\WebDriver\BiDi\Script\StringValue;
use Rnscommerce\WebDriver\BiDi\Session\UnsubscribeByIDRequest;

/**
 * Every command and event of the reduced set, against the browser the subclass starts.
 */
abstract class ProtocolTestCase extends BrowserTestCase
{
    private const DOM_OBSERVER = '(send) => new MutationObserver(() => send(1))'
        . '.observe(document, {subtree: true, childList: true})';

    public function testSubscribeAndUnsubscribe(): void
    {
        $entries = 0;
        $id = (new LogInspector($this->bidi))->onConsoleEntry(function () use (&$entries): void {
            $entries++;
        });

        $this->session->navigate($this->url('console.html'));
        $this->waitFor(
            function () use (&$entries): bool {
                return $entries > 0;
            },
            'a console entry',
        );
        $this->bidi->removeListener($id);

        $this->expectException(ErrorResponseException::class);
        $this->bidi->send(Session::unsubscribe(new UnsubscribeByIDRequest([$id])));
    }

    public function testCallFunctionAwaitsAPromiseAndReturnsATypedValue(): void
    {
        $result = (new Script($this->bidi))->callFunctionInBrowsingContext(
            $this->handle,
            '() => new Promise((resolve) => setTimeout(() => resolve(2), 50))',
            true,
        );

        self::assertInstanceOf(EvaluationResultSuccess::class, $result);
        self::assertInstanceOf(NumberValue::class, $result->result);
        self::assertSame(2, $result->result->value);
    }

    public function testPreloadScriptChannelMessagesReachOnMessage(): void
    {
        $messages = [];
        $script = new Script($this->bidi);
        $script->addPreloadScript(self::DOM_OBSERVER, [new ChannelValue(new ChannelProperties('dom'))]);
        $script->onMessage(function (MessageParameters $message) use (&$messages): void {
            $messages[] = $message;
        });

        $this->session->navigate($this->url('mutate.html'));
        $this->waitFor(
            function () use (&$messages): bool {
                return $messages !== [];
            },
            'a script message',
        );

        self::assertSame('dom', $messages[0]->channel);
        self::assertInstanceOf(NumberValue::class, $messages[0]->data);
        self::assertSame(1, $messages[0]->data->value);
        self::assertSame($this->handle, $messages[0]->source->context);
    }

    public function testRemovedPreloadScriptSendsNothing(): void
    {
        $messages = 0;
        $script = new Script($this->bidi);
        $id = $script->addPreloadScript(self::DOM_OBSERVER, [new ChannelValue(new ChannelProperties('dom'))]);
        $script->onMessage(function () use (&$messages): void {
            $messages++;
        });
        $script->removePreloadScript($id);

        $this->session->navigate($this->url('mutate.html'));
        $this->settle(1.0);

        self::assertSame(0, $messages);
    }

    public function testGetTreeDescribesTheContext(): void
    {
        $this->session->navigate($this->url('probe.html'));

        $tree = (new BrowsingContext($this->bidi, $this->handle))->getTree();

        self::assertCount(1, $tree);
        self::assertSame($this->handle, $tree[0]->context);
        self::assertSame($this->url('probe.html'), $tree[0]->url);
    }

    public function testCreateAndCloseATab(): void
    {
        $tab = BrowsingContext::create($this->bidi, new CreateParameters(CreateType::TAB));

        self::assertNotSame($this->handle, $tab->getId());
        self::assertSame($tab->getId(), $tab->getTree()[0]->context);

        $tab->close();

        try {
            $tab->getTree();
            self::fail('The closed tab still has a tree');
        } catch (ErrorResponseException $e) {
            self::assertSame('no such frame', $e->getError());
        }
    }

    public function testCaptureScreenshotReturnsAPng(): void
    {
        $this->session->navigate($this->url('probe.html'));

        $png = base64_decode((new BrowsingContext($this->bidi, $this->handle))->captureScreenshot(), true);

        self::assertIsString($png);
        self::assertStringStartsWith("\x89PNG\r\n\x1a\n", $png);
    }

    public function testScreencastStartsAndStops(): void
    {
        $this->session->navigate($this->url('transition.html'));
        $context = new BrowsingContext($this->bidi, $this->handle);

        try {
            $started = $context->startScreencast();
            $this->settle(0.5);
            $stopped = $context->stopScreencast($started->screencast);
        } catch (ErrorResponseException $e) {
            if (in_array($e->getError(), ['unknown command', 'unsupported operation'], true)) {
                self::markTestSkipped("{$e->getError()}: {$e->getMessage()}");
            }
            throw $e;
        }

        self::assertNotSame('', $started->screencast);
        self::assertFileExists($stopped->path);
        unlink($stopped->path);
    }

    public function testATabInAUserContextDoesNotSeeTheDefaultContextsStorage(): void
    {
        $this->session->navigate($this->url('state.html'));
        $script = new Script($this->bidi);
        $read = 'JSON.stringify({cookie: document.cookie, storage: localStorage.getItem("fixture")})';

        $default = $this->callString($script, $this->handle, "() => $read");
        self::assertSame('{"cookie":"fixture=1","storage":"1"}', $default);

        $browser = new Browser($this->bidi);
        $userContext = $browser->createUserContext();
        $tab = BrowsingContext::create($this->bidi, new CreateParameters(CreateType::TAB, userContext: $userContext));
        self::assertSame($userContext, $tab->getTree()[0]->userContext);

        $this->session->switchToWindow($tab->getId());
        $this->session->navigate($this->url('probe.html'));
        $isolated = $this->callString($script, $tab->getId(), "() => $read");
        self::assertSame('{"cookie":"","storage":null}', $isolated);

        $tab->close();
        $browser->removeUserContext($userContext);
    }

    public function testDataCollectorKeepsAResponseBody(): void
    {
        $network = new Network($this->bidi);
        $requestId = null;
        $network->onResponseCompleted(function (ResponseCompletedParameters $event) use (&$requestId): void {
            if (str_ends_with($event->request->url, '/data.json')) {
                $requestId = $event->request->request;
            }
        });
        $collector = $network->addDataCollector(
            new AddDataCollectorParameters([DataType::RESPONSE], 1000000, CollectorType::BLOB),
        );

        $this->session->navigate($this->url('fetch.html'));
        $this->waitFor(
            function () use (&$requestId): bool {
                return $requestId !== null;
            },
            'the data.json response',
        );
        self::assertIsString($requestId);

        $body = $network->getData(new GetDataParameters(DataType::RESPONSE, $requestId, $collector));
        $text = match (true) {
            $body instanceof NetworkString => $body->value,
            $body instanceof Base64Value => (string) base64_decode($body->value, true),
            default => self::fail('Unknown bytes value'),
        };
        self::assertSame('{"fixture":"body"}', trim($text));

        $network->removeDataCollector($collector);
        $this->expectException(ErrorResponseException::class);
        $network->getData(new GetDataParameters(DataType::RESPONSE, $requestId, $collector));
    }

    public function testNavigationEventsArriveInOrder(): void
    {
        $seen = [];
        $inspector = new BrowsingContextInspector($this->bidi, [$this->handle]);
        $inspector->onNavigationStarted(function (NavigationInfo $info) use (&$seen): void {
            $seen[] = ['navigationStarted', $info];
        });
        $inspector->onDomContentLoaded(function (NavigationInfo $info) use (&$seen): void {
            $seen[] = ['domContentLoaded', $info];
        });
        $inspector->onBrowsingContextLoaded(function (NavigationInfo $info) use (&$seen): void {
            $seen[] = ['load', $info];
        });

        $this->session->navigate($this->url('probe.html'));
        $this->waitFor(
            function () use (&$seen): bool {
                return count($seen) >= 3;
            },
            'navigation events',
        );

        self::assertSame(['navigationStarted', 'domContentLoaded', 'load'], array_column($seen, 0));
        foreach ($seen as [, $info]) {
            self::assertSame($this->handle, $info->context);
            self::assertSame($this->url('probe.html'), $info->url);
        }
    }

    public function testContextCreatedArrivesForANewTab(): void
    {
        $created = [];
        (new BrowsingContextInspector($this->bidi))->onBrowsingContextCreated(
            function (ContextCreatedParameters $info) use (&$created): void {
                $created[] = $info->context;
            },
        );

        $tab = BrowsingContext::create($this->bidi, new CreateParameters(CreateType::TAB));
        $this->waitFor(
            function () use ($tab, &$created): bool {
                return in_array($tab->getId(), $created, true);
            },
            'contextCreated',
        );

        self::assertContains($tab->getId(), $created);
        $tab->close();
    }

    public function testNetworkEventsDescribeARequest(): void
    {
        $before = $started = $completed = [];
        $network = new Network($this->bidi, [$this->handle]);
        $network->onBeforeRequestSent(function (BeforeRequestSentParameters $e) use (&$before): void {
            $before[] = $e;
        });
        $network->onResponseStarted(function (ResponseStartedParameters $e) use (&$started): void {
            $started[] = $e;
        });
        $network->onResponseCompleted(function (ResponseCompletedParameters $e) use (&$completed): void {
            $completed[] = $e;
        });

        $this->session->navigate($this->url('fetch.html'));
        $url = $this->url('data.json');
        $this->waitFor(
            function () use (&$completed, $url): bool {
                return array_filter($completed, static fn ($e): bool => $e->request->url === $url) !== [];
            },
            'the data.json response',
        );

        $sent = array_values(array_filter($before, static fn ($e): bool => $e->request->url === $url));
        $begun = array_values(array_filter($started, static fn ($e): bool => $e->request->url === $url));
        self::assertCount(1, $sent);
        self::assertSame('GET', $sent[0]->request->method);
        self::assertSame($this->handle, $sent[0]->context);
        self::assertCount(1, $begun);
        self::assertSame(200, $begun[0]->response->status);
    }

    public function testFetchErrorReportsAFetchToAClosedPort(): void
    {
        $port = self::freePort();
        $errors = [];
        $network = new Network($this->bidi, [$this->handle]);
        $network->onFetchError(function (FetchErrorParameters $e) use (&$errors): void {
            $errors[] = $e;
        });

        $this->session->navigate($this->url("fetcherror.html?$port"));
        $this->waitFor(
            function () use (&$errors): bool {
                return $errors !== [];
            },
            'a fetch error',
        );

        self::assertSame("http://127.0.0.1:$port/refused", $errors[0]->request->url);
        self::assertNotSame('', $errors[0]->errorText);
    }

    public function testLogEntriesCarryConsoleCallsAndUncaughtErrors(): void
    {
        $console = $errors = [];
        $log = new LogInspector($this->bidi, [$this->handle]);
        $log->onConsoleEntry(function (ConsoleLogEntry $e) use (&$console): void {
            $console[] = $e;
        });
        $log->onJavaScriptException(function (JavascriptLogEntry $e) use (&$errors): void {
            $errors[] = $e;
        });

        $this->session->navigate($this->url('console.html'));
        $this->session->navigate($this->url('error.html'));
        $this->waitFor(
            function () use (&$console, &$errors): bool {
                return $console !== [] && $errors !== [];
            },
            'a console entry and an error',
        );

        self::assertSame('hello from fixture', $console[0]->text);
        self::assertSame('log', $console[0]->method);
        self::assertStringContainsString('fixture failure', (string) $errors[0]->text);
    }

    public function testTwoConnectionsHearTheSameEventAndOutliveEachOther(): void
    {
        $second = $this->connect();
        $first = $heard = [];
        (new LogInspector($this->bidi))->onConsoleEntry(function (ConsoleLogEntry $e) use (&$first): void {
            $first[] = $e->text;
        });
        (new LogInspector($second))->onConsoleEntry(function (ConsoleLogEntry $e) use (&$heard): void {
            $heard[] = $e->text;
        });

        $this->session->navigate($this->url('console.html'));
        $this->waitFor(
            function () use (&$first, &$heard): bool {
                return $first !== [] && $heard !== [];
            },
            'both connections to hear',
            10.0,
        );
        $heardFirst = $first;
        self::assertSame(['hello from fixture'], $heardFirst);
        self::assertSame(['hello from fixture'], $heard);

        $second->close();
        $this->session->navigate($this->url('console.html'));
        $this->waitFor(
            function () use (&$first): bool {
                return count($first) === 2;
            },
            'the first connection to hear again',
        );
        self::assertCount(1, (new BrowsingContext($this->bidi, $this->handle))->getTree());
    }

    public function testEventsThatQueueDuringABlockingCallAreDeliveredByDrain(): void
    {
        $texts = [];
        $started = 0;
        (new LogInspector($this->bidi))->onConsoleEntry(function (ConsoleLogEntry $e) use (&$texts): void {
            $texts[] = $e->text;
        });
        (new BrowsingContextInspector($this->bidi))->onNavigationStarted(function () use (&$started): void {
            $started++;
        });

        $this->session->navigate($this->url('console.html'));
        usleep(300000);
        self::assertSame([], $texts);
        $this->bidi->drain();

        self::assertSame(['hello from fixture'], $texts);
        self::assertSame(1, $started);
    }

    public function testDrainDeliversAMessageLargerThanOneSocketRead(): void
    {
        $lengths = [];
        (new LogInspector($this->bidi))->onConsoleEntry(function (ConsoleLogEntry $e) use (&$lengths): void {
            $lengths[] = strlen((string) $e->text);
        });

        $this->session->navigate($this->url('big.html'));
        usleep(500000);
        $this->bidi->drain();

        self::assertSame([200000], $lengths);
    }

    public function testAMessageLargerThanTenMegabytesArrivesAndTheConnectionCarriesOn(): void
    {
        $lengths = [];
        (new LogInspector($this->bidi))->onConsoleEntry(function (ConsoleLogEntry $e) use (&$lengths): void {
            $lengths[] = strlen((string) $e->text);
        });

        $this->session->navigate($this->url('huge.html'));
        $this->waitFor(
            function () use (&$lengths): bool {
                return $lengths !== [];
            },
            'the 11 MB entry',
        );

        self::assertSame([11 * 1024 * 1024], $lengths);
        self::assertCount(1, (new BrowsingContext($this->bidi, $this->handle))->getTree());
    }

    public function testDeletingTheSessionClosesTheConnectionAndEveryUseSaysWhy(): void
    {
        (new LogInspector($this->bidi))->onConsoleEntry(static function (): void {
        });

        $this->session->delete();
        try {
            $this->waitFor(static fn (): bool => false, 'the connection to close');
            self::fail('The connection did not close');
        } catch (ConnectionClosedException $closed) {
            self::assertStringContainsString('closed by the remote end', $closed->getMessage());
            self::assertInstanceOf(ConnectionClosedException::class, $closed->getPrevious());
        }

        self::assertTrue($this->bidi->isClosed());
        try {
            $this->bidi->send(Session::unsubscribe(new UnsubscribeByIDRequest(['none'])));
            self::fail('send() did not throw');
        } catch (ConnectionClosedException $e) {
            self::assertSame($closed, $e);
        }
    }

    public function testClearingListenersAfterTheSessionIsDeletedDoesNotThrow(): void
    {
        (new LogInspector($this->bidi))->onConsoleEntry(static function (): void {
        });
        (new Network($this->bidi))->onBeforeRequestSent(static function (): void {
        });

        $this->session->delete();
        $started = microtime(true);
        $this->bidi->clearListeners();

        self::assertLessThan(5.0, microtime(true) - $started);
    }

    public function testListenersScopedToDifferentContextsHearOnlyTheirOwn(): void
    {
        $tab = BrowsingContext::create($this->bidi, new CreateParameters(CreateType::TAB));
        $heardByA = $heardByB = [];
        $inA = new LogInspector($this->bidi, [$this->handle]);
        $inA->onConsoleEntry(function (ConsoleLogEntry $e) use (&$heardByA): void {
            $heardByA[] = $e->text;
        });
        $inB = new LogInspector($this->bidi, [$tab->getId()]);
        $inB->onConsoleEntry(function (ConsoleLogEntry $e) use (&$heardByB): void {
            $heardByB[] = $e->text;
        });

        $script = new Script($this->bidi);
        $script->callFunctionInBrowsingContext($this->handle, '() => console.log("from A")', false);
        $script->callFunctionInBrowsingContext($tab->getId(), '() => console.log("from B")', false);
        $this->waitFor(
            function () use (&$heardByA, &$heardByB): bool {
                return in_array('from A', $heardByA, true) && in_array('from B', $heardByB, true);
            },
            'both entries',
        );
        $this->settle(0.3);

        if ($heardByA !== ['from A'] || $heardByB !== ['from B']) {
            self::markTestIncomplete(
                'Scoped listeners leak: A heard ' . json_encode($heardByA) . ', B heard ' . json_encode($heardByB),
            );
        }
        self::assertSame(['from A'], $heardByA);
        self::assertSame(['from B'], $heardByB);
        $tab->close();
    }

    private function callString(Script $script, string $context, string $function): string
    {
        $result = $script->callFunctionInBrowsingContext($context, $function, false);
        self::assertInstanceOf(EvaluationResultSuccess::class, $result);
        self::assertInstanceOf(StringValue::class, $result->result);
        return $result->result->value;
    }
}
