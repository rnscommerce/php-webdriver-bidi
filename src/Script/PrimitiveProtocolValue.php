<?php

/**
 * Generated from schema/schema.json — do not edit, run composer generate.
 */

declare(strict_types=1);

namespace Rnscommerce\WebDriver\BiDi\Script;

interface PrimitiveProtocolValue extends LocalValue, RemoteValue
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
