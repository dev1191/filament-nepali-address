<?php

namespace Dev1191\FilamentNepaliAddress;

use Illuminate\Database\Schema\Blueprint;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentNepaliAddressServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-nepali-address';

    public function configurePackage(Package $package): void
    {
        $package->name(static::$name);

        if (file_exists($package->basePath('/../config/nepali-address.php'))) {
            $package->hasConfigFile('nepali-address');
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }
    }

    public function packageBooted(): void
    {
        // Load translations under 'nepali-address' namespace
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nepali-address');

        // Register database schema Blueprint macros
        $this->registerBlueprintMacros();
    }

    protected function registerBlueprintMacros(): void
    {
        if (! Blueprint::hasMacro('nepaliAddress')) {
            Blueprint::macro('nepaliAddress', function (?string $prefix = null, bool $withWard = true, bool $withPostalCode = true): void {
                /** @var Blueprint $this */
                $p = $prefix ?? '';
                $this->unsignedSmallInteger($p . 'province_id')->nullable()->index();
                $this->unsignedSmallInteger($p . 'district_id')->nullable()->index();
                $this->unsignedSmallInteger($p . 'municipality_id')->nullable()->index();

                if ($withWard) {
                    $this->unsignedSmallInteger($p . 'ward_no')->nullable();
                }

                if ($withPostalCode) {
                    $this->string($p . 'postal_code', 10)->nullable();
                }
            });
        }

        if (! Blueprint::hasMacro('dropNepaliAddress')) {
            Blueprint::macro('dropNepaliAddress', function (?string $prefix = null, bool $withWard = true, bool $withPostalCode = true): void {
                /** @var Blueprint $this */
                $p = $prefix ?? '';
                $columns = [
                    $p . 'province_id',
                    $p . 'district_id',
                    $p . 'municipality_id',
                ];

                if ($withWard) {
                    $columns[] = $p . 'ward_no';
                }

                if ($withPostalCode) {
                    $columns[] = $p . 'postal_code';
                }

                $this->dropIndex([$p . 'province_id']);
                $this->dropIndex([$p . 'district_id']);
                $this->dropIndex([$p . 'municipality_id']);

                $this->dropColumn($columns);
            });
        }
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_nepali_address_table',
        ];
    }
}
