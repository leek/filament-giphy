<?php

declare(strict_types=1);

namespace Leek\FilamentGiphy;

use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentGiphyServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-giphy';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('giphy', __DIR__.'/../resources/css/giphy.css'),
            Js::make('rich-content-plugins/giphy', __DIR__.'/../resources/js/dist/filament/rich-content-plugins/giphy.js')->loadedOnRequest(),
        ], 'leek/filament-giphy');
    }
}
