<?php

namespace Dev1191\FilamentNepaliAddress;

use Dev1191\FilamentNepaliAddress\Commands\FilamentNepaliAddressCommand;
use Dev1191\FilamentNepaliAddress\Testing\TestsFilamentNepaliAddress;
use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentNepaliAddressServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-nepali-address';

    public static string $viewNamespace = 'filament-nepali-address';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('dev1191/filament-nepali-address');
            });

        if (file_exists($package->basePath('/../config/nepali-address.php'))) {
            $package->hasConfigFile('nepali-address');
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void {}

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-nepali-address/{$file->getFilename()}"),
                ], 'filament-nepali-address-stubs');
            }
        }

        // Testing
        Testable::mixin(new TestsFilamentNepaliAddress);

        // Load translations under 'nepali-address' namespace as well
        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'nepali-address');
    }

    protected function getAssetPackageName(): ?string
    {
        return 'dev1191/filament-nepali-address';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-nepali-address', __DIR__ . '/../resources/dist/components/filament-nepali-address.js'),
            // Css::make('filament-nepali-address-styles', __DIR__ . '/../resources/dist/filament-nepali-address.css'),
            // Js::make('filament-nepali-address-scripts', __DIR__ . '/../resources/dist/filament-nepali-address.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            FilamentNepaliAddressCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
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
