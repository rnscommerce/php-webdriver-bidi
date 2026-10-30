<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Session;

enum UserPromptHandlerType: string
{
    case ACCEPT = 'accept';
    case DISMISS = 'dismiss';
    case IGNORE = 'ignore';
}
