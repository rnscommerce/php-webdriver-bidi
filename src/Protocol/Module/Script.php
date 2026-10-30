<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Protocol\Module;

use Rnscommerce\WebDriver\BiDi\Command;
use Rnscommerce\WebDriver\BiDi\Event;
use Rnscommerce\WebDriver\BiDi\Script\AddPreloadScriptParameters;
use Rnscommerce\WebDriver\BiDi\Script\AddPreloadScriptResult;
use Rnscommerce\WebDriver\BiDi\Script\CallFunctionParameters;
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResult;
use Rnscommerce\WebDriver\BiDi\Script\EvaluationResultFactory;
use Rnscommerce\WebDriver\BiDi\Script\MessageParameters;
use Rnscommerce\WebDriver\BiDi\Script\RemovePreloadScriptParameters;

final class Script
{
    /**
     * @return Event<MessageParameters>
     */
    public static function message(): Event
    {
        return new Event('script.message', MessageParameters::fromArray(...));
    }

    /**
     * @return Command<AddPreloadScriptResult>
     */
    public static function addPreloadScript(AddPreloadScriptParameters $params): Command
    {
        return new Command(
            'script.addPreloadScript',
            $params->toArray(),
            AddPreloadScriptResult::fromArray(...),
        );
    }

    /**
     * @return Command<EvaluationResult>
     */
    public static function callFunction(CallFunctionParameters $params): Command
    {
        return new Command(
            'script.callFunction',
            $params->toArray(),
            EvaluationResultFactory::fromArray(...),
        );
    }

    /**
     * @return Command<null>
     */
    public static function removePreloadScript(RemovePreloadScriptParameters $params): Command
    {
        return new Command(
            'script.removePreloadScript',
            $params->toArray(),
            static fn (array $result): null => null,
        );
    }
}
