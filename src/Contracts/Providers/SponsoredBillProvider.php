<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\BillCollection;

/**
 * Lists the bills sponsored by one legislator.
 *
 * Answers "what has this member introduced?" directly, rather than forcing a
 * consumer to scan every bill's sponsors to build the inverse index.
 */
interface SponsoredBillProvider
{
    public function sponsoredBills(string|int $personId): BillCollection;
}
