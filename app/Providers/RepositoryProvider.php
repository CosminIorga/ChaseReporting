<?php

namespace App\Providers;

use App\Repositories\ConfigRepository;
use App\Repositories\DataRepository;
use App\Repositories\RedisRepository;
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

        $this->app->singleton(RedisRepository::class, function () {
            return new RedisRepository();
        });
    }
}
