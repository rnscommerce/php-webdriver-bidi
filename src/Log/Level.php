<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Log;

enum Level: string
{
    case DEBUG = 'debug';
    case INFO = 'info';
    case WARN = 'warn';
    case ERROR = 'error';
}
