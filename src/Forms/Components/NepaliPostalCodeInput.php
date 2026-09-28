<?php

namespace Dev1191\FilamentNepaliAddress\Forms\Components;

use Filament\Forms\Components\TextInput;
use Khanaldpk\NepaliAddress\Rules\NepalPostalCode;

class NepaliPostalCodeInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('nepali-address::nepali-address.postal_code'))
            ->placeholder('e.g. 44600')
            ->length(5)
            ->rule(new NepalPostalCode);
    }
}
