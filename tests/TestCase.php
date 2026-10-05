<?php

namespace FLAIRUK\World\Tests;

use FLAIRUK\Aircrafts\AircraftsServiceProvider;
use FLAIRUK\Airlines\AirlinesServiceProvider;
use FLAIRUK\Airports\AirportsServiceProvider;
use FLAIRUK\Cities\CitiesServiceProvider;
use FLAIRUK\Countries\CountriesServiceProvider;
use FLAIRUK\World\Facades\World;
use FLAIRUK\World\WorldServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            CountriesServiceProvider::class,
            CitiesServiceProvider::class,
            AirportsServiceProvider::class,
            AirlinesServiceProvider::class,
            AircraftsServiceProvider::class,
            WorldServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app): array
    {
        return ['World' => World::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }
}
