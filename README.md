# Laravel Lobbyist

Search, monitor, and summarize legislative activity — bills, votes, and elected
representatives — across the United States, through a single driver-agnostic API.

This is the **core** package. It ships the contract, the driver manager, and the
normalized data objects, but no data source of its own. You install one or more
**driver packages** that plug in behind it:

| Package | Role |
| --- | --- |
| [`wiserwebsolutions/laravel-lobbyist-legiscan`](https://github.com/wiserwebsolutions/laravel-lobbyist-legiscan) | Default nationwide driver (LegiScan API) |
| [`wiserwebsolutions/laravel-lobbyist-palegis`](https://github.com/wiserwebsolutions/laravel-lobbyist-palegis) | Pennsylvania driver (palegis.us RSS feeds) |

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

// Uses the LegiScan default driver (nationwide coverage).
$ca = Lobbyist::state('CA');
$bills = $ca->bills();            // BillCollection
$bill = $ca->bill('AB1');         // Bill (lookup by number)

// Uses the PA driver if laravel-palegis is installed, else the LegiScan default.
$pa = Lobbyist::state('PA');
$votes = $pa->votes();               // VoteCollection
$house = $pa->representatives();     // LegislatorCollection, House only
$senate = $pa->senators();           // LegislatorCollection, Senate only
$all = $pa->legislators();           // LegislatorCollection, both chambers
```

Which operations a driver supports varies by source — check first (see below).

### Chambers

Every driver also exposes its state's legislative chambers as a fluent, chamber-scoped
entry point. Each chamber delegates back to the driver's `bills()`/`votes()`/`legislators()`
and filters the result to that chamber — so you never have to call `->byChamber()` yourself:

```php
$pa = Lobbyist::state('PA');

$pa->chambers();                             // ChamberCollection (House, then Senate)
$pa->chambers()->first()->bills();           // BillCollection, House only
$pa->chambers()->first()->votes();           // VoteCollection, House only
$pa->chambers()->first()->representatives(); // LegislatorCollection, House only
```

(A chamber's own `representatives()` always means "the legislators belonging to
this chamber" — for the Senate `ChamberContext` that's the same members
`$pa->senators()` returns; only the driver-level `representatives()` means
"House members specifically".)

`chambers()` defaults to `[House, Senate]` for every driver (bicameral, which covers all
currently shipped drivers). Calling a chamber-scoped method the driver doesn't back throws
`UnsupportedOperationException`, exactly like calling it directly on the driver.

Each chamber also exposes its computed political `lean()`, based on the party affiliation
of its representatives:

```php
$house = $pa->chambers()->first();

(string) $house->lean();      // e.g. "Slight Democrat", "Strong Republican", "Neutral"
$house->lean()->detail();     // e.g. "Slight Democrat (120 Democrats, 100 Republicans, 4 Independents)"
```

The label compares the two major parties' share of the two-party total (independents/others
are excluded from the comparison, but included in `detail()`): a spread under 10 points is
`"Neutral"`, 10–30 points is `"Slight {Party}"`, and over 30 points is `"Strong {Party}"`.
`lean()` throws `UnsupportedOperationException` under the same condition as this
chamber's `representatives()`, since it's built from that same data.

### Sessions

Every driver also exposes the most recent legislative session directly:

```php
$pa->session(); // Session — the most recent non-prior, non-sine-die session
```

Throws `UnsupportedOperationException` if the driver doesn't implement `SessionProvider`, or
a `LobbyistException` if it does but reports no sessions at all.

### Capabilities

Not every data source supports every operation — an RSS feed can *list* current
bills but cannot *look up* an arbitrary bill by id. Drivers therefore implement
only the capabilities they can back, and you can check before calling:

```php
use WiserWebSolutions\Lobbyist\Contracts\Capability;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;

$driver = Lobbyist::state('CA'); // LegiScan

if ($driver->supports(Capability::GetBill)) {
    $bill = $driver->bill(1132030); // Bill
}

// or type-check the segregated interface directly:
if ($driver instanceof BillLookup) {
    $bill = $driver->bill('AB1');
}
```

Calling an unsupported lookup throws `UnsupportedOperationException`.

| Capability | Method | Interface | LegiScan | PA (RSS) |
| --- | --- | --- | :---: | :---: |
| `ListSessions` | `sessions()` | `SessionProvider` | ✅ | ✅ |
| `ListBills` | `bills()` | `BillProvider` | ✅ | ✅ |
| `GetBill` | `bill($id)` | `BillLookup` | ✅ | ✅ |
| `ListVotes` | `votes()` | `VoteProvider` | — | ✅ |
| `GetVote` | `vote($id)` | `VoteLookup` | ✅ | — |
| `ListLegislators` | `senators()` / `representatives()` / `legislators()` | `LegislatorProvider` | ✅ | ✅ |
| `GetRepresentative` | `representative($id)` | `RepresentativeLookup` | ✅ | — |
| `GetBillText` | `billText($id)` | `BillTextLookup` | ✅ | ✅ |
| `ListBillTextHistory` | `billTextHistory($id)` | `BillTextHistoryLookup` | ✅ | ✅ |

### Bill text

`billText($id)` returns the bill's current (most recent) text version;
`billTextHistory($id)` returns every version (introduced, amended, enrolled,
etc.) as a `BillTextCollection`:

```php
$ca = Lobbyist::state('CA');

$ca->billText(1132030);          // BillText — the most recent version
$ca->billTextHistory(1132030);   // BillTextCollection — every version

$text = $ca->billText(1132030);
$text->type;      // e.g. "Enrolled"
$text->url;        // link to view/download this version
$text->content;    // the document's bytes, if the driver fetched them — otherwise null
```

`content` is `null` whenever a driver only has a link to the document (e.g. a
PDF) rather than its fetched bytes — fetch `url` yourself in that case.
`BillTextCollection::latest()` picks the most recent entry by date, which is
what `billText()` is typically built from.

Every `Bill` a driver returns also carries the same version data directly, no
driver call required — a mapper embeds it while building the `Bill` in the
first place:

```php
$bill = $ca->bill(1132030);

$bill->texts();   // BillTextCollection — every version, oldest first
$bill->text();    // BillText — the most recent version; never null

$bill->text()->toHTML();   // link to the HTML rendering (throws if unavailable)
$bill->text()->toPDF();    // link to the PDF rendering (throws if unavailable)
$bill->text()->toString(); // the literal text (throws unless content was fetched)
```

`Bill::text()`/`texts()` are pure reads of whatever the mapper already
attached — they never perform I/O themselves, so `toString()` throws unless a
driver already populated `content` (which `billText($id)` does, fetching just
the latest version's bytes). `toHTML()`/`toPDF()` throw only when that
particular version doesn't have a link in that format at all.

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
   It also gives you `chambers()` for free, defaulting to `[House, Senate]`; override
   the protected `$chambers` property if your state has a unicameral legislature. `session()`
   and each chamber's `lean()` are also free, built on top of `sessions()`/`legislators()`
   — implement `SessionProvider`/`LegislatorProvider` and both work automatically.
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
