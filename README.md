# Laravel Lobbyist

Search, monitor, and summarize legislative activity — bills, votes, and elected
representatives — across the United States, through a single driver-agnostic API.

This is the **core** package. It ships the contract, the driver manager, and the
normalized data objects, but no data source of its own. You install one or more
**driver packages** that plug in behind it:

| Package | Role |
| --- | --- |
| [`wiserwebsolutions/laravel-lobbyist-legiscan`](https://github.com/wiserwebsolutions/laravel-lobbyist-legiscan) | Default nationwide driver (LegiScan API) |
| [`wiserwebsolutions/laravel-palegis`](https://github.com/wiserwebsolutions/laravel-palegis) | Pennsylvania driver (palegis.us RSS feeds) |

## Installation

```bash
composer require wiserwebsolutions/laravel-lobbyist
# plus at least one driver — the default nationwide driver:
composer require wiserwebsolutions/laravel-lobbyist-legiscan
```

Publish the config if you want to change the default driver:

```bash
php artisan vendor:publish --tag=lobbyist-config
```

## Usage

`Lobbyist::state($abbr)` resolves the driver registered for that state, falling
back to the default driver (`legiscan`) when no state-specific driver is
installed. The returned object is scoped to that state.

```php
use WiserWebSolutions\Lobbyist\Facades\Lobbyist;

// Uses the PA driver if laravel-palegis is installed, else the LegiScan default.
$driver = Lobbyist::state('PA');

$bills = $driver->listBills();       // BillCollection
$votes = $driver->listVotes();       // VoteCollection
$people = $driver->listRepresentatives(); // LegislatorCollection
```

### Capabilities

Not every data source supports every operation — an RSS feed can *list* current
bills but cannot *look up* an arbitrary bill by id. Drivers therefore implement
only the capabilities they can back, and you can check before calling:

```php
use WiserWebSolutions\Lobbyist\Contracts\Capability;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;

$driver = Lobbyist::state('CA'); // LegiScan

if ($driver->supports(Capability::GetBill)) {
    $bill = $driver->getBill(1132030); // Bill
}

// or type-check the segregated interface directly:
if ($driver instanceof BillLookup) {
    $bill = $driver->getBill('AB1');
}
```

Calling an unsupported lookup throws `UnsupportedOperationException`.

| Capability | Interface | LegiScan | PA (RSS) |
| --- | --- | :---: | :---: |
| `ListSessions` | `SessionProvider` | ✅ | ✅ |
| `ListBills` | `BillProvider` | ✅ | ✅ |
| `GetBill` | `BillLookup` | ✅ | — |
| `ListVotes` | `VoteProvider` | — | ✅ |
| `GetVote` | `VoteLookup` | ✅ | — |
| `ListRepresentatives` | `RepresentativeProvider` | ✅ | ✅ |
| `GetRepresentative` | `RepresentativeLookup` | ✅ | — |

## Data objects

Drivers return normalized [spatie/laravel-data](https://spatie.be/docs/laravel-data)
objects — `Session`, `Bill`, `Vote`, `Legislator` — regardless of source. States
are typed via the `StateEnum`, chambers via `Chamber`, parties via `Party`.

These objects are **provider-agnostic**: each derives its typed properties from a
documented, normalized `meta` array (see the class docblocks for the recognized
keys) and is unaware of any specific data source. It is a driver's job to map its
raw payload into that shape — so adding a new provider never requires touching
core. Drivers typically keep the raw payload under `meta['raw']` so nothing is lost.

## Writing a state driver

1. Create a package that requires `wiserwebsolutions/laravel-lobbyist`.
2. Write a driver extending `WiserWebSolutions\Lobbyist\Support\AbstractDriver`
   and implementing the provider/lookup interfaces you can actually back.
   `AbstractDriver` derives `capabilities()`/`supports()` from those interfaces
   automatically and throws `UnsupportedOperationException` for lookups you omit.
3. Map your source's raw payloads into the core DTOs' normalized `meta` shape —
   keep this in a mapper class in *your* package (see `LegiscanMapper` /
   `PalegisMapper` for reference). Core never learns about your source.
4. Register it from your service provider's `boot()`, order-independently:

   ```php
   $this->app->resolving('lobbyist', function ($manager) {
       $manager->extend('pa', fn () => new PaDriver(/* ... */));
   });
   ```

5. Verify compliance with the shipped
   `WiserWebSolutions\Lobbyist\Testing\AssertsDriverContract` trait.

## Testing

```bash
composer install
vendor/bin/phpunit
```

## License

MIT © Daniel Wiser
