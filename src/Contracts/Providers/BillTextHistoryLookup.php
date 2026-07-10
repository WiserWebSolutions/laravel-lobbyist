<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\BillTextCollection;

/**
 * Fetches every version of a single bill's text (introduced, amended,
 * enrolled, etc.) by identifier.
 */
interface BillTextHistoryLookup
{
    public function billTextHistory(string|int $billIdentifier): BillTextCollection;
}
