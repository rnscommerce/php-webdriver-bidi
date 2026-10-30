<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Module;

use Rnscommerce\WebDriver\BiDi\BrowsingContext\CaptureScreenshotParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CaptureScreenshotResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CloseParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\ContextCreatedParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\CreateResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\GetTreeParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\GetTreeResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\NavigationInfo;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StartScreencastParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StartScreencastResult;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StopScreencastParameters;
use Rnscommerce\WebDriver\BiDi\BrowsingContext\StopScreencastResult;
use Rnscommerce\WebDriver\BiDi\Command;
use Rnscommerce\WebDriver\BiDi\Event;

final class BrowsingContext
{
    /**
     * @return Event<ContextCreatedParameters>
     */
    public static function contextCreated(): Event
    {
        return new Event('browsingContext.contextCreated', ContextCreatedParameters::fromArray(...));
    }

    /**
     * @return Event<NavigationInfo>
     */
    public static function domContentLoaded(): Event
    {
        return new Event('browsingContext.domContentLoaded', NavigationInfo::fromArray(...));
    }

    /**
     * @return Event<NavigationInfo>
     */
    public static function load(): Event
    {
        return new Event('browsingContext.load', NavigationInfo::fromArray(...));
    }

    /**
     * @return Event<NavigationInfo>
     */
    public static function navigationStarted(): Event
    {
        return new Event('browsingContext.navigationStarted', NavigationInfo::fromArray(...));
    }

    /**
     * @return Command<CaptureScreenshotResult>
     */
    public static function captureScreenshot(CaptureScreenshotParameters $params): Command
    {
        return new Command(
            'browsingContext.captureScreenshot',
            $params->toArray(),
            CaptureScreenshotResult::fromArray(...),
        );
    }

    /**
     * @return Command<null>
     */
    public static function close(CloseParameters $params): Command
    {
        return new Command(
            'browsingContext.close',
            $params->toArray(),
            static fn (array $result): null => null,
        );
    }

    /**
     * @return Command<CreateResult>
     */
    public static function create(CreateParameters $params): Command
    {
        return new Command(
            'browsingContext.create',
            $params->toArray(),
            CreateResult::fromArray(...),
        );
    }

    /**
     * @return Command<GetTreeResult>
     */
    public static function getTree(GetTreeParameters $params): Command
    {
        return new Command(
            'browsingContext.getTree',
            $params->toArray(),
            GetTreeResult::fromArray(...),
        );
    }

    /**
     * @return Command<StartScreencastResult>
     */
    public static function startScreencast(StartScreencastParameters $params): Command
    {
        return new Command(
            'browsingContext.startScreencast',
            $params->toArray(),
            StartScreencastResult::fromArray(...),
        );
    }

    /**
     * @return Command<StopScreencastResult>
     */
    public static function stopScreencast(StopScreencastParameters $params): Command
    {
        return new Command(
            'browsingContext.stopScreencast',
            $params->toArray(),
            StopScreencastResult::fromArray(...),
        );
    }
}
