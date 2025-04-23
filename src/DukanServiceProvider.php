<?php

namespace Tocaan\Dukan;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Tocaan\Dukan\Events\TenantStatusChanged;
use Tocaan\Dukan\Listeners\TenantStatusLogListener;
use Tocaan\Dukan\Services\AwsService;
use Tocaan\Dukan\Services\CloudflareService;
use Tocaan\Dukan\Services\PloiService;

class DukanServiceProvider extends ServiceProvider
{
    public function boot()
    {

        Event::listen(
            TenantStatusChanged::class,
            [TenantStatusLogListener::class, 'handle']
        );
        $this->publishes([
            __DIR__ . '/../config/dukan.php' => config_path('dukan.php'),
        ], 'config');
        $this->publishes([
            __DIR__ . '/../Database/Migrations' => database_path('migrations'),
        ], 'migrations');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'dukan');
        $this->publishes([
            __DIR__ . '/../config/dukan.php' => config_path('dukan.php'),
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton(AwsService::class, function ($app) {
            return new AwsService();
        });

        $this->app->singleton(CloudflareService::class, function ($app) {
            return new CloudflareService();
        });

        $this->app->singleton(PloiService::class, function ($app) {
            return new PloiService();
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/dukan.php',
            'dukan'
        );
    }
}
