<?php

namespace WiserWebSolutions\Lobbyist\Exceptions;

use RuntimeException;

/**
 * Base exception for the Lobbyist ecosystem. Driver packages may extend this
 * with source-specific factories (e.g. a LegiScan API error).
 */
class LobbyistException extends RuntimeException
{
    /**
     * A configuration value required by a driver is missing.
     */
    public static function missingConfig(string $key): self
    {
        return new self("Missing required Lobbyist configuration value: [{$key}].");
    }

    /**
     * A driver reported an error while talking to its data source.
     */
    public static function driverError(string $message): self
    {
        return new self($message);
    }
}
