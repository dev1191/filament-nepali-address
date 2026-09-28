<?php

namespace Dev1191\FilamentNepaliAddress\Commands;

use Illuminate\Console\Command;

class FilamentNepaliAddressCommand extends Command
{
    public $signature = 'filament-nepali-address';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
