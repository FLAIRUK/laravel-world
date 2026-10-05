# Changelog

All notable changes to `laravel-world` will be documented in this file.

## 1.0.1 - 2026-10-05

- `citiesIn()` and `airportsIn()` return lists, so a `CountryProfile` always serialises `cities` and `airports` as JSON arrays; they were objects keyed by code, or `[]` when empty.
- README: requirements.

## 1.0.0 - 2026-10-05

First release.

- Requires the five FLAIRUK data packages at 1.0: countries, cities, airports, airlines and aircraft.
- `World` facade: `country()` and `countryOrFail()` by alpha-2, alpha-3 or numeric code; `citiesIn()`, `airportsIn()`, `airlinesIn()`; `countryOf()`; `profile()`; `code()`; `search()`; and accessors for each dataset.
- `CountryProfile`, a country with its cities, airports and airlines, serialisable to JSON.
- `world:install` and `world:seed` commands covering all five tables.
- Tests and GitHub Actions CI on PHP 8.2–8.5 with Laravel 12 and 13.
