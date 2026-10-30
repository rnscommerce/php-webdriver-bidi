<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

enum InitiatorType: string
{
    case PARSER = 'parser';
    case SCRIPT = 'script';
    case PREFLIGHT = 'preflight';
    case OTHER = 'other';
}
