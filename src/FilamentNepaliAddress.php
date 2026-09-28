<?php

namespace Dev1191\FilamentNepaliAddress;

use Dev1191\FilamentNepaliAddress\Support\AddressData;

/**
 * Proxy helper to AddressData.
 *
 * @mixin AddressData
 */
class FilamentNepaliAddress
{
    /**
     * @param  array<mixed>  $arguments
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return AddressData::{$name}(...$arguments);
    }
}
