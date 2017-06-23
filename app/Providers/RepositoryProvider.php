<?php

namespace App\Providers;

use App\Repositories\ConfigRepository;
use App\Repositories\DataRepository;
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
        $this->app->singleton(ConfigRepository::class, function () {
            return new ConfigRepository();
        });

        $this->app->singleton(DataRepository::class, function () {
            return new DataRepository();
        });
    }
}
