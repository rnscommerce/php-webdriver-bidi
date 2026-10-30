<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Rnscommerce\WebDriver\BiDi\Browser\CreateUserContextParameters;
use Rnscommerce\WebDriver\BiDi\Browser\RemoveUserContextParameters;
use Rnscommerce\WebDriver\BiDi\Browser\UserContextInfo;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\BoxClipRectangle;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CaptureScreenshotParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CaptureScreenshotParametersOrigin;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CaptureScreenshotResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CloseParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateType;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\GetTreeParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\GetTreeResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\ImageFormat;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\MediaTrackConstraints;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StartScreencastParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StartScreencastResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StopScreencastParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StopScreencastResult;
use Rnscommerce\WebDriver\BiDi\Command;
use Rnscommerce\WebDriver\BiDi\Module\Browser;
use Rnscommerce\WebDriver\BiDi\Module\BrowsingContext;
use Rnscommerce\WebDriver\BiDi\Module\Network;
use Rnscommerce\WebDriver\BiDi\Module\Script;
use Rnscommerce\WebDriver\BiDi\Module\Session;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorResult;
use Rnscommerce\WebDriver\BiDi\Network\CollectorType;
use Rnscommerce\WebDriver\BiDi\Network\DataType;
use Rnscommerce\WebDriver\BiDi\Network\GetDataParameters;
use Rnscommerce\WebDriver\BiDi\Network\GetDataResult;
use Rnscommerce\WebDriver\BiDi\Network\RemoveDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Script\AddPreloadScriptParameters;
use Rnscommerce\WebDriver\BiDi\Script\AddPreloadScriptResult;
use Rnscommerce\WebDriver\BiDi\Script\CallFunctionParameters;
use Rnscommerce\WebDriver\BiDi\Script\ChannelProperties;
use Rnscommerce\WebDriver\BiDi\Script\ChannelValue;
use Rnscommerce\WebDriver\BiDi\Script\ContextTarget;
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResultSuccess;
use Rnscommerce\WebDriver\BiDi\Script\RemovePreloadScriptParameters;
use Rnscommerce\WebDriver\BiDi\Script\ResultOwnership;
use Rnscommerce\WebDriver\BiDi\Script\SerializationOptions;
use Rnscommerce\WebDriver\BiDi\Script\SharedReference;
use Rnscommerce\WebDriver\BiDi\Script\StringValue;
use Rnscommerce\WebDriver\BiDi\Session\DirectProxyConfiguration;
use Rnscommerce\WebDriver\BiDi\Session\SubscribeParameters;
use Rnscommerce\WebDriver\BiDi\Session\SubscribeResult;
use Rnscommerce\WebDriver\BiDi\Session\UnsubscribeByIDRequest;
use Rnscommerce\WebDriver\BiDi\Session\UserPromptHandler;
use Rnscommerce\WebDriver\BiDi\Session\UserPromptHandlerType;

final class CommandsTest extends TestCase
{
    use WireAssertions;

    private const CONTEXT = 'B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40';

    /**
     * @param Command<covariant mixed> $command
     * @param class-string|null $resultClass
     */
    #[DataProvider('commands')]
    public function testCommand(
        Command $command,
        string $method,
        string $params,
        string $result,
        ?string $resultClass,
    ): void {
        self::assertSame($method, $command->getMethod());
        self::assertEncodesTo(self::json($params), $command->getParams());

        $mapped = $command->mapResult(self::json($result));
        if ($resultClass === null) {
            self::assertNull($mapped);
            return;
        }
        self::assertInstanceOf($resultClass, $mapped);
        self::assertEncodesTo(self::json($result), $mapped);
    }

    /**
     * @return iterable<string, array{Command<covariant mixed>, string, string, string, class-string|null}>
     */
    public static function commands(): iterable
    {
        yield 'session.subscribe' => [
            Session::subscribe(new SubscribeParameters(
                events: ['network.beforeRequestSent', 'browsingContext.load'],
                contexts: [self::CONTEXT],
            )),
            'session.subscribe',
            '{"events": ["network.beforeRequestSent", "browsingContext.load"], "contexts": ["' . self::CONTEXT . '"]}',
            '{"subscription": "9a2e4d1c-3f5b-4c7e-8d6a-1b0f2e3c4d5e"}',
            SubscribeResult::class,
        ];
        yield 'session.unsubscribe' => [
            Session::unsubscribe(new UnsubscribeByIDRequest(['9a2e4d1c-3f5b-4c7e-8d6a-1b0f2e3c4d5e'])),
            'session.unsubscribe',
            '{"subscriptions": ["9a2e4d1c-3f5b-4c7e-8d6a-1b0f2e3c4d5e"]}',
            '{}',
            null,
        ];
        yield 'script.callFunction' => [
            Script::callFunction(new CallFunctionParameters(
                functionDeclaration: '(element, selector) => element.querySelectorAll(selector).length',
                awaitPromise: true,
                target: new ContextTarget(self::CONTEXT),
                arguments: [new SharedReference('f.B5F38C3A.d.4D1E2F30.e.17'), new StringValue('.loading-mask')],
                resultOwnership: ResultOwnership::NONE,
                serializationOptions: new SerializationOptions(maxObjectDepth: 1),
                userActivation: false,
            )),
            'script.callFunction',
            <<<'JSON'
                {
                    "functionDeclaration": "(element, selector) => element.querySelectorAll(selector).length",
                    "awaitPromise": true,
                    "target": {"context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40"},
                    "arguments": [
                        {"sharedId": "f.B5F38C3A.d.4D1E2F30.e.17"},
                        {"type": "string", "value": ".loading-mask"}
                    ],
                    "resultOwnership": "none",
                    "serializationOptions": {"maxObjectDepth": 1},
                    "userActivation": false
                }
                JSON,
            <<<'JSON'
                {
                    "type": "success",
                    "realm": "6c0f8c35-3e8b-4a5e-b3c1-7b1a2d9e4f60",
                    "result": {
                        "type": "object",
                        "value": [
                            ["pending", {"type": "number", "value": 2}],
                            ["finished", {"type": "number", "value": "-0"}],
                            ["ready", {"type": "boolean", "value": false}]
                        ]
                    }
                }
                JSON,
            EvaluationResultSuccess::class,
        ];
        yield 'script.addPreloadScript' => [
            Script::addPreloadScript(new AddPreloadScriptParameters(
                functionDeclaration: '(send) => new MutationObserver(() => send(1)).observe(document)',
                arguments: [new ChannelValue(new ChannelProperties('mutations', ownership: ResultOwnership::ROOT))],
            )),
            'script.addPreloadScript',
            <<<'JSON'
                {
                    "functionDeclaration": "(send) => new MutationObserver(() => send(1)).observe(document)",
                    "arguments": [{"type": "channel", "value": {"channel": "mutations", "ownership": "root"}}]
                }
                JSON,
            '{"script": "1f4c2b7e-0a9d-4e3c-8b5f-6d2a1c0e9b8a"}',
            AddPreloadScriptResult::class,
        ];
        yield 'script.removePreloadScript' => [
            Script::removePreloadScript(new RemovePreloadScriptParameters('1f4c2b7e-0a9d-4e3c-8b5f-6d2a1c0e9b8a')),
            'script.removePreloadScript',
            '{"script": "1f4c2b7e-0a9d-4e3c-8b5f-6d2a1c0e9b8a"}',
            '{}',
            null,
        ];
        yield 'browsingContext.getTree' => [
            BrowsingContext::getTree(new GetTreeParameters(maxDepth: 0)),
            'browsingContext.getTree',
            '{"maxDepth": 0}',
            <<<'JSON'
                {
                    "contexts": [{
                        "children": null,
                        "clientWindow": "4B1C7E2D9F3A0B6C",
                        "context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40",
                        "originalOpener": null,
                        "url": "https://magento.test/customer/account/login/",
                        "userContext": "default"
                    }]
                }
                JSON,
            GetTreeResult::class,
        ];
        yield 'browsingContext.create' => [
            BrowsingContext::create(new CreateParameters(CreateType::TAB, background: true, userContext: 'default')),
            'browsingContext.create',
            '{"type": "tab", "background": true, "userContext": "default"}',
            '{"context": "0D7E9A1F2C3B4E5D6A7B8C9D0E1F2A3B", "userContext": "default"}',
            CreateResult::class,
        ];
        yield 'browsingContext.close' => [
            BrowsingContext::close(new CloseParameters(self::CONTEXT, promptUnload: false)),
            'browsingContext.close',
            '{"context": "' . self::CONTEXT . '", "promptUnload": false}',
            '{}',
            null,
        ];
        yield 'browsingContext.captureScreenshot' => [
            BrowsingContext::captureScreenshot(new CaptureScreenshotParameters(
                context: self::CONTEXT,
                origin: CaptureScreenshotParametersOrigin::VIEWPORT,
                format: new ImageFormat('image/jpeg', quality: 0.8),
                clip: new BoxClipRectangle(0, 0, 1280, 720.5),
            )),
            'browsingContext.captureScreenshot',
            <<<'JSON'
                {
                    "context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40",
                    "origin": "viewport",
                    "format": {"type": "image/jpeg", "quality": 0.8},
                    "clip": {"type": "box", "x": 0, "y": 0, "width": 1280, "height": 720.5}
                }
                JSON,
            '{"data": "/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8U"}',
            CaptureScreenshotResult::class,
        ];
        yield 'browsingContext.startScreencast' => [
            BrowsingContext::startScreencast(new StartScreencastParameters(
                context: self::CONTEXT,
                mimeType: 'video/webm',
                video: new MediaTrackConstraints(width: 1280, height: 720, frameRate: 25),
            )),
            'browsingContext.startScreencast',
            <<<'JSON'
                {
                    "context": "B5F38C3A1D0E4A0C9A6E2F9C7D3B1A40",
                    "mimeType": "video/webm",
                    "video": {"width": 1280, "height": 720, "frameRate": 25}
                }
                JSON,
            '{"screencast": "c3d9e1f0-7a2b-4c5d-9e8f-0a1b2c3d4e5f", "path": "/tmp/screencasts/c3d9e1f0.webm"}',
            StartScreencastResult::class,
        ];
        yield 'browsingContext.stopScreencast' => [
            BrowsingContext::stopScreencast(new StopScreencastParameters('c3d9e1f0-7a2b-4c5d-9e8f-0a1b2c3d4e5f')),
            'browsingContext.stopScreencast',
            '{"screencast": "c3d9e1f0-7a2b-4c5d-9e8f-0a1b2c3d4e5f"}',
            '{"path": "/tmp/screencasts/c3d9e1f0.webm"}',
            StopScreencastResult::class,
        ];
        yield 'browser.createUserContext' => [
            Browser::createUserContext(new CreateUserContextParameters(
                acceptInsecureCerts: true,
                proxy: new DirectProxyConfiguration(),
                unhandledPromptBehavior: new UserPromptHandler(default: UserPromptHandlerType::DISMISS),
            )),
            'browser.createUserContext',
            <<<'JSON'
                {
                    "acceptInsecureCerts": true,
                    "proxy": {"proxyType": "direct"},
                    "unhandledPromptBehavior": {"default": "dismiss"}
                }
                JSON,
            '{"userContext": "8E3F1A2B4C5D6E7F8091A2B3C4D5E6F7"}',
            UserContextInfo::class,
        ];
        yield 'browser.removeUserContext' => [
            Browser::removeUserContext(new RemoveUserContextParameters('8E3F1A2B4C5D6E7F8091A2B3C4D5E6F7')),
            'browser.removeUserContext',
            '{"userContext": "8E3F1A2B4C5D6E7F8091A2B3C4D5E6F7"}',
            '{}',
            null,
        ];
        yield 'network.addDataCollector' => [
            Network::addDataCollector(new AddDataCollectorParameters(
                dataTypes: [DataType::RESPONSE],
                maxEncodedDataSize: 10000000,
                collectorType: CollectorType::BLOB,
            )),
            'network.addDataCollector',
            '{"dataTypes": ["response"], "maxEncodedDataSize": 10000000, "collectorType": "blob"}',
            '{"collector": "5b6c7d8e-9f0a-4b1c-8d2e-3f4a5b6c7d8e"}',
            AddDataCollectorResult::class,
        ];
        yield 'network.getData' => [
            Network::getData(new GetDataParameters(
                dataType: DataType::RESPONSE,
                request: '13716.42',
                collector: '5b6c7d8e-9f0a-4b1c-8d2e-3f4a5b6c7d8e',
                disown: true,
            )),
            'network.getData',
            <<<'JSON'
                {
                    "dataType": "response",
                    "request": "13716.42",
                    "collector": "5b6c7d8e-9f0a-4b1c-8d2e-3f4a5b6c7d8e",
                    "disown": true
                }
                JSON,
            '{"bytes": {"type": "string", "value": "{\"success\":true,\"cart\":{\"summary_count\":1}}"}}',
            GetDataResult::class,
        ];
        yield 'network.removeDataCollector' => [
            Network::removeDataCollector(new RemoveDataCollectorParameters('5b6c7d8e-9f0a-4b1c-8d2e-3f4a5b6c7d8e')),
            'network.removeDataCollector',
            '{"collector": "5b6c7d8e-9f0a-4b1c-8d2e-3f4a5b6c7d8e"}',
            '{}',
            null,
        ];
    }

    public function testCallFunctionResultIsTyped(): void
    {
        $command = Script::callFunction(
            new CallFunctionParameters('() => document.readyState', false, new ContextTarget(self::CONTEXT)),
        );
        $result = $command->mapResult(
            self::json('{"type": "success", "realm": "6c0f", "result": {"type": "string", "value": "complete"}}'),
        );

        self::assertInstanceOf(EvaluationResultSuccess::class, $result);
        self::assertInstanceOf(StringValue::class, $result->result);
        self::assertSame('complete', $result->result->value);
    }
}
