<?php

namespace RealZone22\PenguTables;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
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

        Route::get('/pengutables/export/{token}', function (string $token) {
            $export = Cache::pull('pengutables.export.'.$token);

            abort_unless(is_array($export), 404);

            $component = ($export['componentIsNamed'] ?? false)
                ? app('livewire')->new($export['component'])
                : app($export['component']);
            $component->boot();

            return $component->exportFromState($export['state'], $export['type']);
        })->name(config('pengutables.export_route', 'pengutables.export'));
    }
}
