<?php

namespace WiserWebSolutions\Lobbyist\Data\Concerns;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Throwable;

/**
 * Shared, null-safe coercion helpers for hydrating DTOs from loosely-typed
 * driver payloads (LegiScan JSON, PA RSS items, etc.).
 */
trait ParsesValues
{
    protected static function parseDate(mixed $value): ?CarbonInterface
    {
        if ($value === null || $value === '' || $value === '0000-00-00') {
            return null;
        }

        try {
            return CarbonImmutable::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    protected static function parseIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected static function parseString(mixed $value): string
    {
        return (string) ($value ?? '');
    }
}
