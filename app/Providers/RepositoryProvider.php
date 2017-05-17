<?php

namespace App\Providers;

use App\Repositories\DataRepository;
use App\Repositories\ReportingRepository as CommonRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(CommonRepository::class, function () {
            return new CommonRepository();
        });

        $this->app->singleton(DataRepository::class, function () {
            return new DataRepository();
        });
    }
}
