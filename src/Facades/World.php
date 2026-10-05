<?php

namespace FLAIRUK\World\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \FLAIRUK\Countries\Countries countries()
 * @method static \FLAIRUK\Cities\Cities cities()
 * @method static \FLAIRUK\Airports\Airports airports()
 * @method static \FLAIRUK\Airlines\Airlines airlines()
 * @method static \FLAIRUK\Aircrafts\Aircrafts aircraft()
 * @method static \FLAIRUK\Countries\Data\Country|null country(\FLAIRUK\Countries\Data\Country|string|int $country)
 * @method static \FLAIRUK\Countries\Data\Country countryOrFail(\FLAIRUK\Countries\Data\Country|string|int $country)
 * @method static \Illuminate\Support\Collection<int, \FLAIRUK\Cities\Data\City> citiesIn(\FLAIRUK\Countries\Data\Country|string|int $country)
 * @method static \Illuminate\Support\Collection<int, \FLAIRUK\Airports\Data\Airport> airportsIn(\FLAIRUK\Countries\Data\Country|string|int $country)
 * @method static \Illuminate\Support\Collection<int, \FLAIRUK\Airlines\Data\Airline> airlinesIn(\FLAIRUK\Countries\Data\Country|string|int $country)
 * @method static \FLAIRUK\Countries\Data\Country|null countryOf(\FLAIRUK\Cities\Data\City|\FLAIRUK\Airports\Data\Airport|\FLAIRUK\Airlines\Data\Airline $place)
 * @method static \FLAIRUK\World\Data\CountryProfile|null profile(\FLAIRUK\Countries\Data\Country|string|int $country)
 * @method static \Illuminate\Support\Collection<string, mixed> code(string $code)
 * @method static \Illuminate\Support\Collection<string, \Illuminate\Support\Collection<int, object>> search(string $term, int $limit = 10)
 *
 * @see \FLAIRUK\World\World
 */
class World extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \FLAIRUK\World\World::class;
    }
}
