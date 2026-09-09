<?php

namespace Xylo\MultiselectTwo;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class XyloMultiselectTwoServiceProvider extends PackageServiceProvider
{
    public static string $name = 'xylo-multiselect-two';

    public static string $viewNamespace = 'xylo-multiselect-two';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews(static::$viewNamespace)
            ->hasTranslations();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register(
            [
                Css::make('xylo-multiselect-two-styles', __DIR__ . '/../resources/dist/xylo-multiselect-two.css'),
            ],
            'xylo/multiselect-two'
        );
    }
}
