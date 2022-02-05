<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

use App\Services\StorageServiceContract;
use App\Services\StorageServiceS3;
use App\Services\Payments\PaymentServiceContract;
use App\Services\Payments\JunoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StorageServiceContract::class, function($app) {
            return $app->make(StorageServiceS3::class);
        });
        $this->app->singleton(PaymentServiceContract::class, function($app) {
            return $app->make(JunoService::class);
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Http::macro('juno', function () {
            return Http::baseUrl(config('services.juno.url'));
        });
    }
}
