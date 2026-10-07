<?php

namespace RealZone22\PenguTables;

use Illuminate\Support\Facades\Blade;
use RealZone22\PenguTables\View\Components\PenguTable;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class PenguTablesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('pengutables')
            ->hasConfigFile()
            ->hasTranslations()
            ->hasViews('pengutables');
    }

    public function packageBooted(): void
    {
        Blade::component('pengutable', PenguTable::class);
    }
}
