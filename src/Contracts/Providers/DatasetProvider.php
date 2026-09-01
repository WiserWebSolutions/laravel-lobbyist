<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\DatasetCollection;

/**
 * Lists the bulk session archives a source publishes.
 *
 * Listing is separate from fetching ({@see DatasetLookup}) because it is cheap
 * and answers the only question that matters most of the time: has this archive
 * changed since it was last imported? Comparing hashes here avoids transferring
 * tens of megabytes to discover nothing is new.
 */
interface DatasetProvider
{
    public function datasets(): DatasetCollection;
}
