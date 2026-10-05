<p align="center">
  <picture>
    <source media="(prefers-color-scheme: dark)" srcset="art/logo-dark.svg">
    <img src="art/logo-light.svg" alt="Laravel World" width="420">
  </picture>
</p>

<h2 align="center">
  <a href="https://www.php.net/" target="_blank"><img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8.2+"></a>&nbsp;
  <a href="https://laravel.com/docs/" target="_blank"><img src="https://img.shields.io/badge/Laravel-12%20%7C%2013-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 12 or 13"></a>&nbsp;
  <a href="https://github.com/FLAIRUK/laravel-world/actions/workflows/tests.yml" target="_blank"><img src="https://img.shields.io/badge/Lint-%E2%9C%93-2EA043?style=flat&logo=githubactions&logoColor=white" alt="Lint"></a>&nbsp;
  <a href="https://github.com/FLAIRUK/laravel-world/actions/workflows/tests.yml" target="_blank"><img src="https://img.shields.io/badge/Tests-%E2%9C%93-2EA043?style=flat&logo=githubactions&logoColor=white" alt="Tests"></a>&nbsp;
  <a href="https://packagist.org/packages/flairuk/laravel-world" target="_blank"><img src="https://img.shields.io/packagist/dt/flairuk/laravel-world?style=flat&logo=packagist&logoColor=white&label=Downloads&color=F28D1A" alt="Downloads on Packagist"></a>&nbsp;
  <a href="https://github.com/FLAIRUK/laravel-world/blob/master/LICENSE" target="_blank"><img src="https://img.shields.io/github/license/FLAIRUK/laravel-world?style=flat&label=License&color=3DA639" alt="MIT licence"></a>&nbsp;
  <a href="https://github.com/FLAIRUK" target="_blank"><img src="https://img.shields.io/badge/Data-IATA%20%2B%20ISO%203166-1D4ED8?style=flat" alt="IATA + ISO 3166"></a>&nbsp;
  <br>&nbsp;
</h2>

**Laravel World** — Countries, cities, airports, airlines and aircraft for Laravel 12 and 13, joined up behind one facade.

- **Every dataset.** Installs all five FLAIRUK data packages, each still usable on its own.
- **Joined through the country.** Find a country by any of its codes, then its cities, airports and airlines.
- **One search.** Search every dataset by code or name in one call.
- **No database required.** Everything is looked up in memory. A command publishes and seeds all five tables when you need them.

<p align="center">
  📦&nbsp;<a href="#-installation">Installation</a> ·
  🧭&nbsp;<a href="#-datasets">Datasets</a> ·
  🚀&nbsp;<a href="#-usage">Usage</a> ·
  💾&nbsp;<a href="#-database-tables-optional">Database tables</a>
</p>

<br><br>

## 📦 Installation

```bash
composer require flairuk/laravel-world
```

Laravel discovers the service providers and the `World` facade automatically.

<br><br>

## 🧭 Datasets

Each dataset is its own package, and `laravel-world` requires all five:

| Package | Data | Facade |
| --- | --- | --- |
| [flairuk/laravel-countries](https://github.com/FLAIRUK/laravel-countries) | ISO 3166 countries | `Countries` |
| [ijeffro/laravel-cities](https://github.com/FLAIRUK/laravel-cities) | IATA city codes | `Cities` |
| [ijeffro/laravel-airports](https://github.com/FLAIRUK/laravel-airports) | IATA airport codes | `Airports` |
| [ijeffro/laravel-airlines](https://github.com/FLAIRUK/laravel-airlines) | IATA airline designators | `Airlines` |
| [ijeffro/laravel-aircrafts](https://github.com/FLAIRUK/laravel-aircrafts) | IATA aircraft type codes | `Aircrafts` |

Their facades, validation rules and models all work as their READMEs describe. `World` adds what none of them can do alone: links between the datasets.

<br><br>

## 🚀 Usage

```php
use FLAIRUK\World\Facades\World;
```

You can also type-hint `FLAIRUK\World\World` to have it injected.

### Countries and what is in them

A country can be given by its alpha-2 (`GB`), alpha-3 (`GBR`) or numeric (`826`) code, or as a `Country` object.

```php
World::country('GBR');               // Country, or null
World::countryOrFail('GB');          // throws ItemNotFoundException for an unknown code

World::citiesIn('GB');               // Collection of City: LON, MAN, EDI, …
World::airportsIn('GBR');            // Collection of Airport: LHR, LGW, MAN, …
World::airlinesIn(826);              // Collection of Airline: BA, VS, …
```

An unknown country gives an empty collection.

`profile()` returns all of it at once, as a `CountryProfile` you can return straight from a controller:

```php
$profile = World::profile('GB');     // CountryProfile, or null

$profile->country;                   // Country
$profile->cities;                    // Collection of City
$profile->airports;                  // Collection of Airport
$profile->airlines;                  // Collection of Airline

return $profile;                     // {"country": {...}, "cities": [...], "airports": [...], "airlines": [...]}
```

### The country of a place

```php
World::countryOf(World::airports()->find('LHR'))->name;     // "United Kingdom"
World::countryOf(World::cities()->find('LON'))->name;       // "United Kingdom"
World::countryOf(World::airlines()->find('BA'))->name;      // "United Kingdom"
```

`countryOf()` returns null for the few airlines with no country.

### What a code could mean

Codes overlap between the datasets. `code()` returns every match for exactly that code, keyed by kind, and leaves out the kinds with no match:

```php
World::code('LHR');      // ['airport' => Airport]
World::code('LON');      // ['city' => City]
World::code('BA');       // ['country' => Bosnia and Herzegovina, 'airlines' => Collection [British Airways]]
World::code('320');      // ['country' => Guatemala (numeric 320), 'aircraft' => Airbus Industrie A320]
```

The keys are `country`, `city`, `airport`, `airlines` and `aircraft`. `airlines` is a collection, because IATA re-issues designators.

### Searching everything

```php
World::search('london');         // ['cities' => [...], 'airports' => [...]]
World::search('london', 3);      // at most three of each
```

`search()` matches each dataset's codes and names, and returns up to ten results per kind (`countries`, `cities`, `airports`, `airlines`, `aircraft`), leaving out the kinds with none.

### Each dataset

The underlying lookups are available from `World` too, so one import is enough:

```php
World::countries()->eea();
World::cities()->find('NYC');
World::airports()->search('Heathrow');
World::airlines()->allWithCode('BA');
World::aircraft()->find('388');
```

These are the same singletons the `Countries`, `Cities`, `Airports`, `Airlines` and `Aircrafts` facades use.

<br><br>

## 💾 Database tables (optional)

To publish all five configs and migrations and then, if you agree, migrate and seed every table:

```bash
php artisan world:install            # asks before migrating
php artisan world:install --migrate  # doesn't ask
```

It skips any migration that an earlier install already published, including one from a dataset's own `*:install` command, so it is safe to run again.

To update the tables after upgrading the packages:

```bash
php artisan world:seed            # insert or update every row
php artisan world:seed --prune    # and delete rows no longer in the data
```

`world:seed` runs each dataset's own seed command: `countries:seed`, `cities:seed`, `airports:seed`, `airlines:seed` and `aircrafts:seed`. Each table's name and connection are set in that dataset's config file.

<br><br>

## 🧪 Testing

```bash
composer test
```

<br><br>

## 📄 License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
