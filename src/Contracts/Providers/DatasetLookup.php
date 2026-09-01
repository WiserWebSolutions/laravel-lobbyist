<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Contracts\DatasetArchive;
use WiserWebSolutions\Lobbyist\Data\Dataset;

/**
 * Downloads one bulk session archive and opens it for reading.
 *
 * This is the cheapest possible way to obtain a whole session. Where a
 * per-record API charges one request per bill and another per roll call, a bulk
 * archive delivers every bill, roll call (including per-member positions) and
 * member for a single request — on a metered API the difference between a
 * backfill that fits the budget and one that consumes most of a month.
 *
 * The trade-off is freshness: sources rebuild archives on a schedule, so an
 * archive lags live data and cannot on its own drive timely change detection.
 * Pair it with {@see BillChangeProvider} for that.
 */
interface DatasetLookup
{
    /**
     * Accepts a {@see Dataset} from a listing, or a bare session identifier
     * when the caller already knows which session it wants.
     */
    public function dataset(Dataset|int|string $session): DatasetArchive;
}
