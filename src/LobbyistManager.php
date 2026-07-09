<?php

namespace WiserWebSolutions\Lobbyist;

use Illuminate\Support\Manager;
use WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver;
use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;

/**
 * Resolves legislative data drivers.
 *
 * Driver packages register themselves against this manager (keyed by state
 * abbreviation, e.g. "pa", or by the default driver name "legiscan") via
 * {@see extend()} — typically from their service provider's boot hook. Core
 * ships no drivers itself.
 */
class LobbyistManager extends Manager
{
    public function getDefaultDriver(): string
    {
        return $this->config->get('lobbyist.drivers.default') ?? 'legiscan';
    }

    /**
     * Route to a state-specific driver if one is installed, otherwise fall
     * back to the default driver, then scope it to the requested state.
     */
    public function state(string $state): LobbyistDriver
    {
        $driverName = strtolower($state);

        try {
            // Prefer a driver registered under the state abbreviation (e.g. 'pa').
            $driver = $this->driver($driverName);
        } catch (\InvalidArgumentException) {
            // No state-specific driver installed — fall back to the default.
            $driver = $this->resolveDefaultDriver();
        }

        return $driver->setStateContext($state);
    }

    /**
     * Resolve the configured default driver with a helpful error if the
     * backing driver package is not installed.
     */
    protected function resolveDefaultDriver(): LobbyistDriver
    {
        $default = $this->getDefaultDriver();

        try {
            return $this->driver($default);
        } catch (\InvalidArgumentException $e) {
            throw new LobbyistException(
                "No Lobbyist driver [{$default}] is registered. Install a driver package "
                ."(e.g. wiserwebsolutions/laravel-lobbyist-legiscan) or register one via Lobbyist::extend().",
                previous: $e
            );
        }
    }
}
