<?php

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\BiDi;
use Rnscommerce\WebDriver\BiDi\Browser\CreateUserContextParameters;
use Rnscommerce\WebDriver\BiDi\Browser\RemoveUserContextParameters;
use Rnscommerce\WebDriver\BiDi\Protocol\Module\Browser as Protocol;

final class Browser
{
    public function __construct(private readonly BiDi $bidi)
    {
    }

    /**
     * @return string the user context id
     */
    public function createUserContext(): string
    {
        return $this->bidi->send(Protocol::createUserContext(new CreateUserContextParameters()))->userContext;
    }

    public function removeUserContext(string $userContext): void
    {
        $this->bidi->send(Protocol::removeUserContext(new RemoveUserContextParameters($userContext)));
    }
}
