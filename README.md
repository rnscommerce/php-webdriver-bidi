# php-webdriver-bidi

A WebDriver BiDi client for PHP. Its naming follows Selenium's Java bindings (`org.openqa.selenium.bidi`), and its command classes are generated from Selenium's BiDi schema.

## Usage

### Connect

Ask for `"webSocketUrl": true` in the capabilities of a new WebDriver session. The session it starts answers with `capabilities.webSocketUrl`, which is what `BiDi::connect` takes:

```php
use Rnscommerce\WebDriver\BiDi\BiDi;

$bidi = BiDi::connect($webSocketUrl);
```

A classic WebDriver window handle is the id of that window's browsing context.

### Listen to network and console events

Each listener subscribes to its event in the browser, in the browsing contexts given or else in all of them, and returns an id that `removeListener` takes to unsubscribe:

```php
use Rnscommerce\WebDriver\BiDi\Log\ConsoleLogEntry;
use Rnscommerce\WebDriver\BiDi\Log\Level;
use Rnscommerce\WebDriver\BiDi\Module\LogInspector;
use Rnscommerce\WebDriver\BiDi\Module\Network;
use Rnscommerce\WebDriver\BiDi\Network\BeforeRequestSentParameters;

$network = new Network($bidi, [$windowHandle]);
$id = $network->onBeforeRequestSent(function (BeforeRequestSentParameters $sent): void {
    echo $sent->request->method, ' ', $sent->request->url, "\n";
});

(new LogInspector($bidi))->onConsoleEntry(function (ConsoleLogEntry $entry): void {
    echo $entry->text, "\n";
}, Level::ERROR);

$bidi->drain();
$bidi->removeListener($id);
```

Events wait on the socket while nothing reads it. They reach their handlers during any command, or when `drain()` is called.

### Run a preload script that reports through a channel

```php
use Rnscommerce\WebDriver\BiDi\Module\Script;
use Rnscommerce\WebDriver\BiDi\Script\ChannelProperties;
use Rnscommerce\WebDriver\BiDi\Script\ChannelValue;
use Rnscommerce\WebDriver\BiDi\Script\MessageParameters;

$script = new Script($bidi);
$script->addPreloadScript(
    '(send) => new MutationObserver(() => send(1)).observe(document, {subtree: true, childList: true})',
    [new ChannelValue(new ChannelProperties('dom'))],
);
$script->onMessage(function (MessageParameters $message): void {
    echo "{$message->channel} changed\n";
});
```

### Call a function in a browsing context

```php
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResultSuccess;
use Rnscommerce\WebDriver\BiDi\Script\NumberValue;

$result = $script->callFunctionInBrowsingContext(
    $windowHandle,
    '() => Promise.all(document.getAnimations().map((a) => a.finished)).then((all) => all.length)',
    true,
);
if ($result instanceof EvaluationResultSuccess && $result->result instanceof NumberValue) {
    echo $result->result->value, " animations finished\n";
}
```

### Take a screenshot

```php
use Rnscommerce\WebDriver\BiDi\BrowsingContext\BrowsingContext;

$png = base64_decode((new BrowsingContext($bidi, $windowHandle))->captureScreenshot());
```

### Open a tab in a user context of its own

A user context keeps its own cookies and storage:

```php
use Rnscommerce\WebDriver\BiDi\BrowsingContext\BrowsingContext;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateType;
use Rnscommerce\WebDriver\BiDi\Module\Browser;

$browser = new Browser($bidi);
$userContext = $browser->createUserContext();
$tab = BrowsingContext::create($bidi, new CreateParameters(CreateType::TAB, userContext: $userContext));
// ...
$tab->close();
$browser->removeUserContext($userContext);
```
