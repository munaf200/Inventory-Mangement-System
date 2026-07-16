<?php

namespace App\Providers;

use App\Models\SupplierPayment;
use App\Observers\SupplierPaymentObserver;
use Filament\Support\Facades\FilamentIcon;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\ServiceProvider;

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
        SupplierPayment::observe(SupplierPaymentObserver::class);
          FilamentIcon::register([
            'panels::pages.dashboard.navigation-item' => Heroicon::Home, 
        ]);
    }
}
