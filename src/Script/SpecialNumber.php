<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

enum SpecialNumber: string
{
    case NAN = 'NaN';
    case _0 = '-0';
    case INFINITY = 'Infinity';
    case _INFINITY = '-Infinity';
}
