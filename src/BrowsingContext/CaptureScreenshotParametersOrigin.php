<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\BrowsingContext;

enum CaptureScreenshotParametersOrigin: string
{
    case VIEWPORT = 'viewport';
    case DOCUMENT = 'document';
}
