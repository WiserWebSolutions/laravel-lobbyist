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
];
