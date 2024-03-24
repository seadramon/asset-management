<?php

namespace Asset\Providers;

use Asset\Repositories\MasterRepository;
use Asset\Repositories\Interfaces\MasterRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
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
        $this->app->bind(
            MasterRepositoryInterface::class, 
            MasterRepository::class
        );
    }
}
