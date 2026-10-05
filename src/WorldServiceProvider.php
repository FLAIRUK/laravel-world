<?php

namespace FLAIRUK\World;

use Illuminate\Support\ServiceProvider;

/**
 * The five data packages register themselves through auto-discovery; this
 * provider only adds the World service on top of them.
 */
class WorldServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(World::class);
        $this->app->alias(World::class, 'world');
    }

    public function boot(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            Console\InstallCommand::class,
            Console\SeedCommand::class,
        ]);
    }
}
