<?php

namespace WiserWebSolutions\Lobbyist\Exceptions;

use WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver;

/**
 * Thrown when a caller invokes an operation the active driver does not support.
 */
class UnsupportedOperationException extends LobbyistException
{
    public static function for(LobbyistDriver $driver, string $operation): self
    {
        $supported = array_map(
            fn ($capability) => $capability->value,
            $driver->capabilities()
        );

        $driverName = class_basename($driver);
        $capabilities = $supported === [] ? 'none' : implode(', ', $supported);

        return new self(
            "The [{$driverName}] driver does not support operation [{$operation}]. "
            ."Supported capabilities: {$capabilities}."
        );
    }
}
