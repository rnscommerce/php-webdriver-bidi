<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\Event;
use Rnscommerce\WebDriver\BiDi\Log\Entry;
use Rnscommerce\WebDriver\BiDi\Log\EntryFactory;

final class Log
{
    /**
     * @return Event<Entry>
     */
    public static function entryAdded(): Event
    {
        return new Event('log.entryAdded', EntryFactory::fromArray(...));
    }
}
