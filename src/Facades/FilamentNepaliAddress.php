<?php

namespace Dev1191\FilamentNepaliAddress\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dev1191\FilamentNepaliAddress\FilamentNepaliAddress
 */
class FilamentNepaliAddress extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Dev1191\FilamentNepaliAddress\FilamentNepaliAddress::class;
    }
}
