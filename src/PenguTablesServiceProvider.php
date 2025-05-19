<?php

namespace RealZone22\PenguTables;

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
            ->hasViews();
    }
}
