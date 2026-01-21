<?php

namespace Plugins\TrustpilotReview;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class TrustpilotPlugin extends ServiceProvider
{
    /**
     * Bootstrap the plugin services.
     *
     * @return void
     */
    public function boot()
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__ . '/routes/api.php');
        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'trustpilot');

        // Publish assets
        $this->publishes([
            __DIR__ . '/resources/js' => public_path('plugins/trustpilot/js'),
            __DIR__ . '/resources/css' => public_path('plugins/trustpilot/css'),
        ], 'trustpilot-assets');

        // Publish config
        $this->publishes([
            __DIR__ . '/config/trustpilot.php' => config_path('trustpilot.php'),
        ], 'trustpilot-config');
    }

    /**
     * Register the plugin services.
     *
     * @return void
     */
    public function register()
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/config/trustpilot.php', 'trustpilot'
        );

        // Register plugin
        $this->app->singleton('trustpilot', function ($app) {
            return new TrustpilotService();
        });
    }
}
