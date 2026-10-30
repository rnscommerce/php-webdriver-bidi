<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\ContextCreatedParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\NavigationInfo;
use Rnscommerce\WebDriver\BiDi\Event;
use Rnscommerce\WebDriver\BiDi\Log\ConsoleLogEntry;
use Rnscommerce\WebDriver\BiDi\Module\BrowsingContext;
use Rnscommerce\WebDriver\BiDi\Module\Log;
use Rnscommerce\WebDriver\BiDi\Module\Network;
use Rnscommerce\WebDriver\BiDi\Module\Script;
use Rnscommerce\WebDriver\BiDi\Network\BeforeRequestSentParameters;
use Rnscommerce\WebDriver\BiDi\Network\FetchErrorParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseCompletedParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseStartedParameters;
use Rnscommerce\WebDriver\BiDi\Network\StringValue;
use Rnscommerce\WebDriver\BiDi\Script\ArrayRemoteValue;
use Rnscommerce\WebDriver\BiDi\Script\MessageParameters;

final class EventsTest extends TestCase
{
    use WireAssertions;

    private const REQUEST = <<<'JSON'
        {
            "request": "13716.42",
            "url": "https://magento.test/customer/section/load/?sections=cart&force_new_section_timestamp=true",
            "method": "GET",
            "headers": [
                {"name": "Accept", "value": {"type": "string", "value": "application/json, text/javascript"}},
                {"name": "X-Requested-With", "value": {"type": "string", "value": "XMLHttpRequest"}}
            ],
            "cookies": [{
                "name": "PHPSESSID",
                "value": {"type": "string", "value": "k2l3m4n5o6p7q8r9s0t1u2v3w4"},
                "domain": "magento.test",
                "path": "/",
                "size": 35,
                "httpOnly": true,
                "secure": true,
                "sameSite": "lax",
                "expiry": 1761854400
            }],
            "headersSize": 412,
            "bodySize": null,
            "destination": "",
            "initiatorType": "xmlhttprequest",
            "timings": {
                "timeOrigin": 1761850800123.4,
                "requestTime": 1761850801456.7,
                "redirectStart": 0,
                "redirectEnd": 0,
                "fetchStart": 1333.3,
                "dnsStart": 0,
                "dnsEnd": 0,
                "connectStart": 0,
                "connectEnd": 0,
                "tlsStart": 0,
                "requestStart": 1334.1,
                "responseStart": 0,
                "responseEnd": 0
            }
        }
        JSON;

    private const RESPONSE = <<<'JSON'
        {
            "url": "https://magento.test/customer/section/load/?sections=cart&force_new_section_timestamp=true",
            "protocol": "h2",
            "status": 200,
            "statusText": "",
            "fromCache": false,
            "headers": [
                {"name": "content-type", "value": {"type": "string", "value": "application/json"}},
                {"name": "x-magento-tags", "value": {"type": "base64", "value": "RlBD"}}
            ],
            "mimeType": "application/json",
            "bytesReceived": 1187,
            "headersSize": 655,
            "bodySize": 532,
            "content": {"size": 1873}
        }
        JSON;

    /**
     * @param Event<covariant mixed> $event
     * @param class-string $paramsClass
     */
    #[DataProvider('events')]
    public function testEvent(Event $event, string $method, string $params, string $paramsClass): void
    {
        self::assertSame($method, $event->getMethod());

        $mapped = $event->mapParams(self::json($params));

        self::assertInstanceOf($paramsClass, $mapped);
        self::assertEncodesTo(self::json($params), $mapped);
    }

    /**
     * @return iterable<string, array{Event<covariant mixed>, string, string, class-string}>
     */
    public static function events(): iterable
    {
        $navigation = <<<'JSON'
            {
                "context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40",
                "navigation": "4c5b1e0d-7d0f-4f63-bb1e-0d5b9d8c1a3e",
                "timestamp": 1761850801502,
                "url": "https://magento.test/customer/account/login/",
                "userContext": "default"
            }
            JSON;
        $sent = '"context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40", "isBlocked": false, "navigation": null,'
            . ' "redirectCount": 0, "timestamp": 1761850801457, "userContext": "default", "request": ' . self::REQUEST;

        yield 'script.message' => [
            Script::message(),
            'script.message',
            <<<'JSON'
                {
                    "channel": "mutations",
                    "data": {
                        "type": "array",
                        "value": [{"type": "number", "value": 3}, {"type": "string", "value": "childList"}]
                    },
                    "source": {"realm": "6c0f8c35-3e8b", "context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40"}
                }
                JSON,
            MessageParameters::class,
        ];
        yield 'browsingContext.contextCreated' => [
            BrowsingContext::contextCreated(),
            'browsingContext.contextCreated',
            <<<'JSON'
                {
                    "children": null,
                    "clientWindow": "4B1C7E2D9F3A0B6C",
                    "context": "7A1C9E3B5D0F2A4C6E8B0D2F4A6C8E0B",
                    "originalOpener": null,
                    "parent": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40",
                    "url": "about:blank",
                    "userContext": "default"
                }
                JSON,
            ContextCreatedParameters::class,
        ];
        yield 'browsingContext.navigationStarted' => [
            BrowsingContext::navigationStarted(),
            'browsingContext.navigationStarted',
            $navigation,
            NavigationInfo::class,
        ];
        yield 'browsingContext.domContentLoaded' => [
            BrowsingContext::domContentLoaded(),
            'browsingContext.domContentLoaded',
            $navigation,
            NavigationInfo::class,
        ];
        yield 'browsingContext.load' => [
            BrowsingContext::load(),
            'browsingContext.load',
            $navigation,
            NavigationInfo::class,
        ];
        yield 'network.beforeRequestSent' => [
            Network::beforeRequestSent(),
            'network.beforeRequestSent',
            '{' . $sent . ', "initiator": {"type": "script", "lineNumber": 18, "columnNumber": 4021}}',
            BeforeRequestSentParameters::class,
        ];
        yield 'network.responseStarted' => [
            Network::responseStarted(),
            'network.responseStarted',
            '{' . $sent . ', "response": ' . self::RESPONSE . '}',
            ResponseStartedParameters::class,
        ];
        yield 'network.responseCompleted' => [
            Network::responseCompleted(),
            'network.responseCompleted',
            '{' . $sent . ', "response": ' . self::RESPONSE . '}',
            ResponseCompletedParameters::class,
        ];
        yield 'network.fetchError' => [
            Network::fetchError(),
            'network.fetchError',
            '{' . $sent . ', "errorText": "net::ERR_ABORTED"}',
            FetchErrorParameters::class,
        ];
        yield 'log.entryAdded' => [
            Log::entryAdded(),
            'log.entryAdded',
            <<<'JSON'
                {
                    "type": "console",
                    "method": "warn",
                    "level": "warn",
                    "text": "Fallback to JQueryUI Compat activated.",
                    "timestamp": 1761850801733,
                    "source": {"realm": "6c0f8c35-3e8b", "context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40"},
                    "args": [{"type": "string", "value": "Fallback to JQueryUI Compat activated."}],
                    "stackTrace": {"callFrames": [{
                        "columnNumber": 16,
                        "functionName": "",
                        "lineNumber": 11,
                        "url": "https://magento.test/static/frontend/Magento/luma/en_US/jquery/compat.js"
                    }]}
                }
                JSON,
            ConsoleLogEntry::class,
        ];
    }

    public function testBeforeRequestSentIsTyped(): void
    {
        $params = Network::beforeRequestSent()->mapParams(self::json(
            '{"context": null, "isBlocked": false, "navigation": null, "redirectCount": 0,'
                . ' "timestamp": 1761850801457, "request": ' . self::REQUEST . '}',
        ));

        self::assertInstanceOf(BeforeRequestSentParameters::class, $params);
        self::assertNull($params->context);
        $header = $params->request->headers[1]->value;
        self::assertInstanceOf(StringValue::class, $header);
        self::assertSame('XMLHttpRequest', $header->value);
        self::assertSame(1761850801456.7, $params->request->timings->requestTime);
    }

    public function testScriptMessageCarriesTheRemoteValue(): void
    {
        $params = Script::message()->mapParams(self::json(
            '{"channel": "mutations", "data": {"type": "array", "value": []}, "source": {"realm": "6c0f"}}',
        ));

        self::assertInstanceOf(ArrayRemoteValue::class, $params->data);
        self::assertSame([], $params->data->value);
    }
}
