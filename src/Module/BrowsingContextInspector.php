<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\ContextCreatedParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\NavigationInfo;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\BrowsingContext as Protocol;

/**
 * Each method subscribes to its event and returns the id that BiDi::removeListener takes.
 */
final class BrowsingContextInspector
{
    /**
     * @param list<string> $browsingContextIds where to listen, or everywhere when empty
     */
    public function __construct(private readonly BiDi $bidi, private readonly array $browsingContextIds = [])
    {
    }

    /**
     * @param callable(ContextCreatedParameters): void $handler
     */
    public function onBrowsingContextCreated(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::contextCreated(), $handler, $this->browsingContextIds);
    }

    /**
     * @param callable(NavigationInfo): void $handler
     */
    public function onNavigationStarted(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::navigationStarted(), $handler, $this->browsingContextIds);
    }

    /**
     * @param callable(NavigationInfo): void $handler
     */
    public function onDomContentLoaded(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::domContentLoaded(), $handler, $this->browsingContextIds);
    }

    /**
     * @param callable(NavigationInfo): void $handler
     */
    public function onBrowsingContextLoaded(callable $handler): string
    {
        return $this->bidi->addListener(Protocol::load(), $handler, $this->browsingContextIds);
    }
}
