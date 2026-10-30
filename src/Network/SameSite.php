<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Network;

enum SameSite: string
{
    case STRICT = 'strict';
    case LAX = 'lax';
    case NONE = 'none';
    case DEFAULT = 'default';
}
