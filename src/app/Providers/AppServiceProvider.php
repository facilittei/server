<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Http;

use App\Services\Storages\StorageServiceContract;
use App\Services\Storages\StorageServiceS3;
use App\Services\Payments\PaymentServiceContract;
use App\Services\Payments\StripeService;
use App\Services\Metrics\MetricContract;
use App\Services\Metrics\PrometheusService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StorageServiceContract::class, function ($app) {
            return $app->make(StorageServiceS3::class);
        });
        $this->app->singleton(PaymentServiceContract::class, function ($app) {
            return $app->make(StripeService::class);
        });
        $this->app->singleton(MetricContract::class, function ($app) {
            return $app->make(PrometheusService::class);
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
            return Http::withHeaders([
                'Content-Type' => 'application/json',
                'X-Api-Version' => config('services.juno.version'),
                'X-Resource-Token' => config('services.juno.resource_token'),
            ])
                ->baseUrl(config('services.juno.url'));
        });
    }
}
