<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\BeforeRequestSentParameters;
use Rnscommerce\WebDriver\BiDi\Network\BytesValue;
use Rnscommerce\WebDriver\BiDi\Network\FetchErrorParameters;
use Rnscommerce\WebDriver\BiDi\Network\GetDataParameters;
use Rnscommerce\WebDriver\BiDi\Network\RemoveDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseCompletedParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseStartedParameters;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\Network as Protocol;

/**
 * Each on method subscribes to its event and returns the id that BiDi::removeListener takes.
 */
final class Network
{
    /**
     * @param list<string> $browsingContextIds where to listen, or everywhere when empty
     */
    public function __construct(private readonly BiDi $bidi, private readonly array $browsingContextIds = [])
    {
    }

    /**
     * @return string the collector id
     */
    public function addDataCollector(AddDataCollectorParameters $parameters): string
    {
        return $this->bidi->send(Protocol::addDataCollector($parameters))->collector;
    }

    public function getData(GetDataParameters $parameters): BytesValue
    {
        return $this->bidi->send(Protocol::getData($parameters))->bytes;
    }

    public function removeDataCollector(string $collector): void
    {
        $this->bidi->send(Protocol::removeDataCollector(new RemoveDataCollectorParameters($collector)));
    }

    /**
     * @param callable(BeforeRequestSentParameters): void $handler
     */
    public function onBeforeRequestSent(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::beforeRequestSent(), $handler, $this->browsingContextIds);
    }

    /**
     * @param callable(ResponseStartedParameters): void $handler
     */
    public function onResponseStarted(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::responseStarted(), $handler, $this->browsingContextIds);
    }

    /**
     * @param callable(ResponseCompletedParameters): void $handler
     */
    public function onResponseCompleted(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::responseCompleted(), $handler, $this->browsingContextIds);
    }

    /**
     * @param callable(FetchErrorParameters): void $handler
     */
    public function onFetchError(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::fetchError(), $handler, $this->browsingContextIds);
    }
}
