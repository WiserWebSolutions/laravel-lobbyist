<?php

namespace WiserWebSolutions\Lobbyist\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver state(string $state)
 * @method static \WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver driver(string|null $driver = null)
 * @method static \WiserWebSolutions\Lobbyist\LobbyistManager extend(string $driver, \Closure $callback)
 * @method static string getDefaultDriver()
 *
 * @see \WiserWebSolutions\Lobbyist\LobbyistManager
 */
class Lobbyist extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'lobbyist';
    }
}
