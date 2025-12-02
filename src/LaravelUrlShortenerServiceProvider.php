<?php

declare(strict_types=1);

namespace Shammaa\LaravelUrlShortener;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class LaravelUrlShortenerServiceProvider extends ServiceProvider
{
    /**
     * Register services
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/url-shortener.php', 'url-shortener');

        // Register services as singletons
        $config = config('url-shortener', []);
        
        $this->app->singleton(
            Services\LinkManager::class,
            fn ($app) => new Services\LinkManager($config)
        );

        $this->app->singleton(
            Services\VisitTracker::class,
            fn ($app) => new Services\VisitTracker($config)
        );

        $this->app->alias(Services\LinkManager::class, 'url-shortener');
    }

    /**
     * Bootstrap services
     */
    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/url-shortener.php' => config_path('url-shortener.php'),
        ], 'url-shortener-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'url-shortener-migrations');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'url-shortener');

        // Register routes
        $this->registerRoutes();

        // Register commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\ClearCacheCommand::class,
            ]);
        }
    }

    /**
     * Register routes
     */
    protected function registerRoutes(): void
    {
        // Register redirect route for short URLs
        Route::middleware(['web'])
            ->prefix(config('url-shortener.prefix', 's'))
            ->group(function () {
                Route::get('/{key}', Http\Controllers\RedirectController::class)
                    ->name('url-shortener.redirect');
                
                // Password check route
                Route::post('/{key}/password', [Http\Controllers\RedirectController::class, 'checkPassword'])
                    ->name('url-shortener.password');
            });

        // Register API routes if enabled
        if (config('url-shortener.api.enabled', true)) {
            Route::middleware(config('url-shortener.api.middleware', ['api']))
                ->prefix(config('url-shortener.api.prefix', 'api/url-shortener'))
                ->group(function () {
                    Route::apiResource('links', Http\Controllers\Api\LinkController::class);
                    Route::get('links/{key}/analytics', [Http\Controllers\Api\LinkController::class, 'analytics'])
                        ->name('api.url-shortener.analytics');
                    Route::get('links/{key}/qr-code', [Http\Controllers\Api\LinkController::class, 'qrCode'])
                        ->name('api.url-shortener.qr-code');
                });
        }
    }
}
