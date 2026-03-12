<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Mantenimiento;
use App\Models\Repuesto;
use App\Models\Reporte;

use App\Observers\MantenimientoObserver;
use App\Observers\RepuestoObserver;
use App\Observers\ReporteObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Mantenimiento::observe(MantenimientoObserver::class);
        Repuesto::observe(RepuestoObserver::class);
        Reporte::observe(ReporteObserver::class);
    }
}
