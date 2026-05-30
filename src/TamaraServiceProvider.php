<?php

namespace Aghfatehi\Tamara;

use Aghfatehi\Tamara\Services\TamaraService;
use Illuminate\Support\ServiceProvider;

class TamaraServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/config/tamara.php' => config_path('tamara.php'),
        ], 'tamara-config');

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'tamara-migrations');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/tamara'),
            ], 'tamara-views');
        }

        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'tamara');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/config/tamara.php', 'tamara');

        $this->app->singleton('tamara', function ($app) {
            return new TamaraService();
        });

        $this->app->alias('tamara', TamaraService::class);
    }

    public function provides(): array
    {
        return ['tamara', TamaraService::class];
    }
}
