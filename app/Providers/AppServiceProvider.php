<?php

namespace App\Providers;

use App\Models\Customer;
use App\Models\CustomerPayment;
use App\Models\Invoice;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Observers\CustomerObserver;
use App\Observers\CustomerPaymentObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PurchaseObserver;
use App\Observers\SupplierObserver;
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
        Customer::observe(CustomerObserver::class);
        Invoice::observe(InvoiceObserver::class);
        CustomerPayment::observe(CustomerPaymentObserver::class);
    Supplier::observe(SupplierObserver::class);
    Purchase::observe(PurchaseObserver::class);
        SupplierPayment::observe(SupplierPaymentObserver::class);
          FilamentIcon::register([
            'panels::pages.dashboard.navigation-item' => Heroicon::Home, 
        ]);
    }
}
