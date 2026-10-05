<?php

namespace FLAIRUK\World\Tests;

use FLAIRUK\Airlines\Airlines;
use FLAIRUK\Airports\Data\Airport;
use FLAIRUK\Cities\Data\City;
use FLAIRUK\Countries\Countries;
use FLAIRUK\World\Data\CountryProfile;
use FLAIRUK\World\Facades\World;
use FLAIRUK\World\World as WorldService;
use Illuminate\Support\ItemNotFoundException;
use PHPUnit\Framework\Attributes\Test;

class WorldTest extends TestCase
{
    #[Test]
    public function it_resolves_a_singleton_through_the_facade_and_alias(): void
    {
        $this->assertSame(app(WorldService::class), app('world'));
        $this->assertInstanceOf(WorldService::class, World::getFacadeRoot());
    }

    #[Test]
    public function it_shares_the_dataset_singletons(): void
    {
        $this->assertSame(app(Countries::class), World::countries());
        $this->assertSame(app(Airlines::class), World::airlines());
    }

    #[Test]
    public function it_finds_a_country_by_any_of_its_codes(): void
    {
        $this->assertSame('GB', World::country('GB')->iso2);
        $this->assertSame('GB', World::country('gbr')->iso2);
        $this->assertSame('GB', World::country(826)->iso2);
        $this->assertNull(World::country('XX'));

        $this->expectException(ItemNotFoundException::class);
        World::countryOrFail('XX');
    }

    #[Test]
    public function it_joins_cities_airports_and_airlines_to_their_country(): void
    {
        $this->assertContains('LON', World::citiesIn('GB')->pluck('code'));
        $this->assertContains('LHR', World::airportsIn('GBR')->pluck('code'));
        $this->assertContains('BA', World::airlinesIn('826')->pluck('code'));

        World::airportsIn('FR')->each(fn (Airport $airport) => $this->assertSame('FR', $airport->countryCode));
        World::citiesIn('FR')->each(fn (City $city) => $this->assertSame('FR', $city->countryCode));
    }

    #[Test]
    public function an_unknown_country_has_nothing_in_it(): void
    {
        $this->assertTrue(World::citiesIn('XX')->isEmpty());
        $this->assertTrue(World::airportsIn('XX')->isEmpty());
        $this->assertTrue(World::airlinesIn('XX')->isEmpty());
        $this->assertNull(World::profile('XX'));
    }

    #[Test]
    public function it_finds_the_country_of_a_place(): void
    {
        $this->assertSame('United Kingdom', World::countryOf(World::airports()->find('LHR'))->name);
        $this->assertSame('United Kingdom', World::countryOf(World::cities()->find('LON'))->name);
        $this->assertSame('United Kingdom', World::countryOf(World::airlines()->find('BA'))->name);
    }

    #[Test]
    public function every_place_belongs_to_a_known_country(): void
    {
        $orphans = World::cities()->all()->concat(World::airports()->all())
            ->filter(fn (City|Airport $place) => World::countryOf($place) === null)
            ->map(fn (City|Airport $place) => "{$place->code} ({$place->countryCode})");

        $this->assertSame([], $orphans->values()->all());
    }

    #[Test]
    public function it_builds_a_country_profile(): void
    {
        $profile = World::profile('GB');

        $this->assertInstanceOf(CountryProfile::class, $profile);
        $this->assertSame('GB', $profile->country->iso2);
        $this->assertContains('LHR', $profile->airports->pluck('code'));

        $json = json_decode(json_encode($profile), true);

        $this->assertSame('GB', $json['country']['iso_3166_2']);
        $this->assertSame($profile->cities->count(), count($json['cities']));
        $this->assertContains('BA', array_column($json['airlines'], 'code'));
    }

    #[Test]
    public function it_says_what_a_code_could_mean(): void
    {
        $this->assertSame(['city'], World::code('LON')->keys()->all());
        $this->assertSame(['airport'], World::code('lhr')->keys()->all());

        // "BA" is Bosnia and Herzegovina's alpha-2 and British Airways' designator.
        $meanings = World::code('BA');
        $this->assertSame(['country', 'airlines'], $meanings->keys()->all());
        $this->assertContains('British Airways', $meanings['airlines']->pluck('name'));

        $this->assertTrue(World::code('§§')->isEmpty());
    }

    #[Test]
    public function it_searches_every_dataset(): void
    {
        $results = World::search('london', 3);

        $this->assertSame(['cities', 'airports'], $results->keys()->all());
        $results->each(fn ($matches) => $this->assertLessThanOrEqual(3, $matches->count()));

        $this->assertArrayHasKey('aircraft', World::search('A320'));
        $this->assertTrue(World::search('')->isEmpty());
    }
}
