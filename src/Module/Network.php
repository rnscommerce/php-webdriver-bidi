<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\Command;
use Rnscommerce\WebDriver\BiDi\Event;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\AddDataCollectorResult;
use Rnscommerce\WebDriver\BiDi\Network\BeforeRequestSentParameters;
use Rnscommerce\WebDriver\BiDi\Network\FetchErrorParameters;
use Rnscommerce\WebDriver\BiDi\Network\GetDataParameters;
use Rnscommerce\WebDriver\BiDi\Network\GetDataResult;
use Rnscommerce\WebDriver\BiDi\Network\RemoveDataCollectorParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseCompletedParameters;
use Rnscommerce\WebDriver\BiDi\Network\ResponseStartedParameters;

final class Network
{
    /**
     * @return Event<BeforeRequestSentParameters>
     */
    public static function beforeRequestSent(): Event
    {
        return new Event('network.beforeRequestSent', BeforeRequestSentParameters::fromArray(...));
    }

    /**
     * @return Event<FetchErrorParameters>
     */
    public static function fetchError(): Event
    {
        return new Event('network.fetchError', FetchErrorParameters::fromArray(...));
    }

    /**
     * @return Event<ResponseCompletedParameters>
     */
    public static function responseCompleted(): Event
    {
        return new Event('network.responseCompleted', ResponseCompletedParameters::fromArray(...));
    }

    /**
     * @return Event<ResponseStartedParameters>
     */
    public static function responseStarted(): Event
    {
        return new Event('network.responseStarted', ResponseStartedParameters::fromArray(...));
    }

    /**
     * @return Command<AddDataCollectorResult>
     */
    public static function addDataCollector(AddDataCollectorParameters $params): Command
    {
        return new Command(
            'network.addDataCollector',
            $params->toArray(),
            AddDataCollectorResult::fromArray(...),
        );
    }

    /**
     * @return Command<GetDataResult>
     */
    public static function getData(GetDataParameters $params): Command
    {
        return new Command(
            'network.getData',
            $params->toArray(),
            GetDataResult::fromArray(...),
        );
    }

    /**
     * @return Command<null>
     */
    public static function removeDataCollector(RemoveDataCollectorParameters $params): Command
    {
        return new Command(
            'network.removeDataCollector',
            $params->toArray(),
            static fn (array $result): null => null,
        );
    }
}
