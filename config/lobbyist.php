<?php

/*
|--------------------------------------------------------------------------
| Lobbyist Configuration
|--------------------------------------------------------------------------
|
| The core package is driver-agnostic. Each driver package (for example
| wiserwebsolutions/laravel-lobbyist-legiscan or a state package such as
| wiserwebsolutions/laravel-palegis) ships and publishes its own config file
| and registers itself against the Lobbyist manager at runtime.
|
*/

return [
    'drivers' => [
        /*
        |----------------------------------------------------------------------
        | Default Driver
        |----------------------------------------------------------------------
        |
        | The driver used by Lobbyist::state() when no driver is registered for
        | the requested state abbreviation. This should match the name a driver
        | package registers itself under (defaults to "legiscan").
        |
        */
        'default' => env('LOBBYIST_DEFAULT_DRIVER', 'legiscan'),
    ],

    /*
    |----------------------------------------------------------------------
    | Legislator Images
    |----------------------------------------------------------------------
    |
    | Legislator::image() downloads and caches a legislator's official
    | portrait (from Legislator::$imageUrl) so it can be handed back as an
    | Illuminate\Http\File. The disk must be a locally-backed filesystem
    | (e.g. "local" or "public") -- Http\File wraps a real path on disk, so
    | a remote disk like S3 won't work here.
    |
    */
    'images' => [
        'disk' => env('LOBBYIST_IMAGES_DISK', 'local'),
        'path' => env('LOBBYIST_IMAGES_PATH', 'lobbyist/legislators'),
    ],
];
