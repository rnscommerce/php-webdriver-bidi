<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\Script as Protocol;
use Rnscommerce\WebDriver\BiDi\Script\AddPreloadScriptParameters;
use Rnscommerce\WebDriver\BiDi\Script\CallFunctionParameters;
use Rnscommerce\WebDriver\BiDi\Script\ChannelValue;
use Rnscommerce\WebDriver\BiDi\Script\ContextTarget;
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResult;
use Rnscommerce\WebDriver\BiDi\Script\LocalValue;
use Rnscommerce\WebDriver\BiDi\Script\MessageParameters;
use Rnscommerce\WebDriver\BiDi\Script\RemovePreloadScriptParameters;

final class Script
{
    /**
     * @param list<string> $browsingContextIds where preload scripts run and messages are listened for,
     *     or everywhere when empty
     */
    public function __construct(private readonly BiDi $bidi, private readonly array $browsingContextIds = [])
    {
    }

    /**
     * Runs the function in every page before the page's own scripts. Each channel argument arrives as a function
     * whose calls reach onMessage.
     *
     * @param list<ChannelValue> $arguments
     * @return string the preload script id
     */
    public function addPreloadScript(string $functionDeclaration, array $arguments = []): string
    {
        return $this->bidi->send(Protocol::addPreloadScript(new AddPreloadScriptParameters(
            $functionDeclaration,
            $arguments === [] ? null : $arguments,
            $this->browsingContextIds === [] ? null : $this->browsingContextIds,
        )))->script;
    }

    public function removePreloadScript(string $id): void
    {
        $this->bidi->send(Protocol::removePreloadScript(new RemovePreloadScriptParameters($id)));
    }

    /**
     * @param list<LocalValue> $arguments
     */
    public function callFunctionInBrowsingContext(
        string $browsingContextId,
        string $functionDeclaration,
        bool $awaitPromise,
        array $arguments = [],
    ): EvaluationResult {
        return $this->bidi->send(Protocol::callFunction(new CallFunctionParameters(
            $functionDeclaration,
            $awaitPromise,
            new ContextTarget($browsingContextId),
            $arguments === [] ? null : $arguments,
        )));
    }

    /**
     * Subscribes to messages sent through channels, and returns the id that BiDi::removeListener takes.
     *
     * @param callable(MessageParameters): void $handler
     */
    public function onMessage(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::message(), $handler, $this->browsingContextIds);
    }
}
