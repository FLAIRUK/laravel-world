<?php

namespace FLAIRUK\World;

use FLAIRUK\Aircrafts\Aircrafts;
use FLAIRUK\Airlines\Airlines;
use FLAIRUK\Airlines\Data\Airline;
use FLAIRUK\Airports\Airports;
use FLAIRUK\Airports\Data\Airport;
use FLAIRUK\Cities\Cities;
use FLAIRUK\Cities\Data\City;
use FLAIRUK\Countries\Countries;
use FLAIRUK\Countries\Data\Country;
use FLAIRUK\World\Data\CountryProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\ItemNotFoundException;

/**
 * One way in to every FLAIRUK dataset, joined through the country.
 *
 * Each dataset stays in its own package (and stays usable on its own); this
 * class only resolves countries by any of their codes and links the others to
 * them. Nothing here touches the database.
 */
class World
{
    public function __construct(
        protected Countries $countries,
        protected Cities $cities,
        protected Airports $airports,
        protected Airlines $airlines,
        protected Aircrafts $aircraft,
    ) {}

    public function countries(): Countries
    {
        return $this->countries;
    }

    public function cities(): Cities
    {
        return $this->cities;
    }

    public function airports(): Airports
    {
        return $this->airports;
    }

    public function airlines(): Airlines
    {
        return $this->airlines;
    }

    public function aircraft(): Aircrafts
    {
        return $this->aircraft;
    }

    /**
     * A country by alpha-2 ("GB"), alpha-3 ("GBR") or numeric ("826") code.
     */
    public function country(Country|string|int $country): ?Country
    {
        return $country instanceof Country ? $country : $this->countries->find($country);
    }

    /**
     * @throws ItemNotFoundException
     */
    public function countryOrFail(Country|string|int $country): Country
    {
        return $this->country($country) ?? throw new ItemNotFoundException("Unknown country code [{$country}].");
    }

    /**
     * @return Collection<int, City>
     */
    public function citiesIn(Country|string|int $country): Collection
    {
        return $this->within($country, fn (string $iso2) => $this->cities->inCountry($iso2));
    }

    /**
     * @return Collection<int, Airport>
     */
    public function airportsIn(Country|string|int $country): Collection
    {
        return $this->within($country, fn (string $iso2) => $this->airports->inCountry($iso2));
    }

    /**
     * @return Collection<int, Airline>
     */
    public function airlinesIn(Country|string|int $country): Collection
    {
        return $this->within($country, fn (string $iso2) => $this->airlines->inCountry($iso2));
    }

    /**
     * The country a city, airport or airline belongs to.
     */
    public function countryOf(City|Airport|Airline $place): ?Country
    {
        return $place->countryCode === null ? null : $this->countries->find($place->countryCode);
    }

    /**
     * A country with its cities, airports and airlines, or null for an unknown code.
     */
    public function profile(Country|string|int $country): ?CountryProfile
    {
        $country = $this->country($country);

        if ($country === null) {
            return null;
        }

        return new CountryProfile(
            country: $country,
            cities: $this->citiesIn($country),
            airports: $this->airportsIn($country),
            airlines: $this->airlinesIn($country),
        );
    }

    /**
     * Everything that uses exactly this code, keyed by kind; kinds with no match are left out.
     *
     * Codes overlap across datasets ("LON" is a city, "BA" an airline and Bosnia's
     * alpha-2), so this answers "what could this code mean?".
     *
     * @return Collection<string, mixed>
     */
    public function code(string $code): Collection
    {
        return collect([
            'country' => $this->countries->find($code),
            'city' => $this->cities->find($code),
            'airport' => $this->airports->find($code),
            'airlines' => ($airlines = $this->airlines->allWithCode($code))->isEmpty() ? null : $airlines,
            'aircraft' => $this->aircraft->find($code),
        ])->filter();
    }

    /**
     * Search every dataset by code or name, keyed by kind; kinds with no match are left out.
     *
     * @return Collection<string, Collection<int, object>>
     */
    public function search(string $term, int $limit = 10): Collection
    {
        return collect([
            'countries' => $this->countries->search($term),
            'cities' => $this->cities->search($term),
            'airports' => $this->airports->search($term),
            'airlines' => $this->airlines->search($term),
            'aircraft' => $this->aircraft->search($term),
        ])
            ->map(fn (Collection $results) => $results->take($limit)->values())
            ->filter(fn (Collection $results) => $results->isNotEmpty());
    }

    /**
     * @template TItem
     *
     * @param  callable(string): Collection<int, TItem>  $lookup
     * @return Collection<int, TItem>
     */
    protected function within(Country|string|int $country, callable $lookup): Collection
    {
        $country = $this->country($country);

        // The datasets key some collections by code; return plain lists so JSON is always an array.
        return $country === null ? new Collection : $lookup($country->iso2)->values();
    }
}
