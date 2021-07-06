<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\StorageServiceContract;
use App\Services\StorageServiceS3;

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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
