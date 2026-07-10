<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\Bill;

/**
 * Fetches a single bill by identifier.
 *
 * Only drivers backed by a queryable API (e.g. LegiScan) implement this;
 * feed-only drivers that cannot look up arbitrary identifiers omit it.
 */
interface BillLookup
{
    public function bill(string|int $identifier): Bill;
}
