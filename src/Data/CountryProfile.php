<?php

namespace FLAIRUK\World\Data;

use FLAIRUK\Airlines\Data\Airline;
use FLAIRUK\Airports\Data\Airport;
use FLAIRUK\Cities\Data\City;
use FLAIRUK\Countries\Data\Country;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use JsonSerializable;

/**
 * A country together with the cities, airports and airlines in it.
 *
 * @implements Arrayable<string, mixed>
 */
final readonly class CountryProfile implements Arrayable, JsonSerializable
{
    /**
     * @param  Collection<int, City>  $cities
     * @param  Collection<int, Airport>  $airports
     * @param  Collection<int, Airline>  $airlines
     */
    public function __construct(
        public Country $country,
        public Collection $cities,
        public Collection $airports,
        public Collection $airlines,
    ) {}

    /**
     * @return array{country: array<string, mixed>, cities: list<array<string, mixed>>, airports: list<array<string, mixed>>, airlines: list<array<string, mixed>>}
     */
    public function toArray(): array
    {
        return [
            'country' => $this->country->toArray(),
            'cities' => $this->cities->map->toArray()->all(),
            'airports' => $this->airports->map->toArray()->all(),
            'airlines' => $this->airlines->map->toArray()->all(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
