<?php

namespace App\Providers;

use App\Repositories\ConfigRepository;
use App\Repositories\DataRepository;
use App\Repositories\ParallelDataRepository;
use App\Repositories\RedisRepository;
use App\Repositories\SerialDataRepository;
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
            $parallelProcessingFlag = config('common.gearman_parallel_processing');

            if ($parallelProcessingFlag) {
                return new ParallelDataRepository();
            }

            return new SerialDataRepository();
        });

        $this->app->singleton(RedisRepository::class, function () {
            return new RedisRepository();
        });
    }
}
