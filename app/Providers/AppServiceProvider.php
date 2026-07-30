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
use Google\Client;
use Google\Service\Drive;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Masbug\Flysystem\GoogleDriveAdapter;

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
        if (!Cache::has('daily_backup_completed')) {
        
        // Background queue/process mein run karein taake App lag/freeze na ho
        dispatch(function () {
            // 1. Database backup banao
            Artisan::call('backup:run', ['--only-db' => true]);

            // 2. Drive par upload karo
            Artisan::call('backup:sync-drive');
        });

        // Cache set kar do 24 ghante ke liye
        Cache::put('daily_backup_completed', true, now()->addDay());
    }
        Storage::extend('google', function ($app, $config) {
            $client = new Client();
            $client->setClientId($config['clientId']);
            $client->setClientSecret($config['clientSecret']);
            $client->RefreshToken($config['refreshToken']);

            $service = new Drive($client);
            $adapter = new GoogleDriveAdapter($service, $config['folder'] ?? '/');
            $driver = new Filesystem($adapter);

            return new FilesystemAdapter($driver, $adapter);
        });
    
        if ($this->app->runningInConsole() === false) {
        Artisan::queue('backup:sync-drive');
    }
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
