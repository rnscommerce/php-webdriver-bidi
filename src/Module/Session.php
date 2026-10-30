<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\Command;
use Rnscommerce\WebDriver\BiDi\Session\SubscribeParameters;
use Rnscommerce\WebDriver\BiDi\Session\SubscribeResult;
use Rnscommerce\WebDriver\BiDi\Session\UnsubscribeParameters;

final class Session
{
    /**
     * @return Command<SubscribeResult>
     */
    public static function subscribe(SubscribeParameters $params): Command
    {
        return new Command(
            'session.subscribe',
            $params->toArray(),
            SubscribeResult::fromArray(...),
        );
    }

    /**
     * @return Command<null>
     */
    public static function unsubscribe(UnsubscribeParameters $params): Command
    {
        return new Command(
            'session.unsubscribe',
            $params->toArray(),
            static fn (array $result): null => null,
        );
    }
}
