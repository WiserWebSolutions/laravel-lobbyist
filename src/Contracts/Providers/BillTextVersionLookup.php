<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\BillText;

/**
 * Fetches one specific version of a bill's text, by that version's own id.
 *
 * Distinct from {@see BillTextLookup}, which takes a bill and returns whichever
 * version is current. Comparing two versions of a bill needs the older one, and
 * a caller that already holds a text history should not have to re-fetch the
 * bill to reach a document it can already name — that is two requests where one
 * would do, and metered APIs charge for both.
 */
interface BillTextVersionLookup
{
    public function billTextVersion(string|int $textIdentifier): BillText;
}
