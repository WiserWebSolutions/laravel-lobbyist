<?php

namespace WiserWebSolutions\Lobbyist\Exceptions;

use RuntimeException;
use Throwable;

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

    /**
     * A request to an upstream data source failed.
     *
     * The message always includes the attempted URL and (when available) the
     * HTTP status, plus guidance for reporting the problem. Callers must ensure
     * the URL contains no secrets (e.g. redact API keys) before passing it here.
     *
     * Returns an instance of the called class, so driver subclasses
     * (LegiscanException, PalegisException, …) produce their own type.
     *
     * @param  string  $url  The attempted URL (secrets redacted)
     * @param  int|null  $status  The HTTP status code, if a response was received
     * @param  string|null  $detail  Extra context (e.g. a hint about the cause)
     */
    public static function requestFailed(string $url, ?int $status = null, ?string $detail = null, ?Throwable $previous = null): static
    {
        $message = "Request to [{$url}] failed";

        if ($status !== null) {
            $message .= " with HTTP status {$status}";
        }

        $message .= '.';

        if ($detail !== null && $detail !== '') {
            $message .= ' '.$detail;
        }

        $message .= ' If the URL above is correct, the upstream service may be'
            .' unavailable or its response format may have changed — please report'
            .' this issue and include the URL.';

        return new static($message, 0, $previous);
    }
}
