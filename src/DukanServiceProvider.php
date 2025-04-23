<?php

namespace Tocaan\Dukan;

use Illuminate\Support\ServiceProvider;

class DukanServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'dukan');
        $this->publishes([
            __DIR__.'/../config/dukan.php' => config_path('dukan.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/dukan.php',
            'dukan'
        );
    }
}
