<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

enum SerializationOptionsIncludeShadowTree: string
{
    case NONE = 'none';
    case OPEN = 'open';
    case ALL = 'all';
}
