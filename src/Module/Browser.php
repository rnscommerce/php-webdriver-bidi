<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\Browser\CreateUserContextParameters;
use Rnscommerce\WebDriver\BiDi\Browser\RemoveUserContextParameters;
use Rnscommerce\WebDriver\BiDi\Browser\UserContextInfo;
use Rnscommerce\WebDriver\BiDi\Command;

final class Browser
{
    /**
     * @return Command<UserContextInfo>
     */
    public static function createUserContext(CreateUserContextParameters $params): Command
    {
        return new Command(
            'browser.createUserContext',
            $params->toArray(),
            UserContextInfo::fromArray(...),
        );
    }

    /**
     * @return Command<null>
     */
    public static function removeUserContext(RemoveUserContextParameters $params): Command
    {
        return new Command(
            'browser.removeUserContext',
            $params->toArray(),
            static fn (array $result): null => null,
        );
    }
}
