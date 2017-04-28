<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Reporting as CommonRepository;

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
    }
}
