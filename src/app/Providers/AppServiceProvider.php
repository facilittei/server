<?php

namespace App\Providers;

use App\Services\Metrics\MetricContract;
use App\Services\Metrics\PrometheusService;
use App\Services\Storages\StorageServiceContract;
use App\Services\Storages\StorageServiceS3;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

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
