<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\BillText;

/**
 * Fetches the current (most recent) text of a single bill by identifier.
 *
 * Cheap to back whenever {@see BillTextHistoryLookup} is available, since the
 * current text is just the most recent entry in the history — but kept as its
 * own capability so a driver can advertise one without the other.
 */
interface BillTextLookup
{
    public function billText(string|int $billIdentifier): BillText;
}
