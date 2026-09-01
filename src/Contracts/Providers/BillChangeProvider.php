<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillCollection;

/**
 * Lists bills in the cheapest form the source offers, for change detection.
 *
 * The returned bills are deliberately shallow: enough to identify each one and
 * read its {@see Bill::$changeHash}, and not
 * necessarily more. Consumers use this to decide which bills are worth a full
 * fetch, which is what keeps a metered API inside its quota — so implementors
 * should back it with the lightest listing operation available rather than the
 * same call used for {@see BillProvider::bills()}.
 */
interface BillChangeProvider
{
    public function billChanges(): BillCollection;
}
