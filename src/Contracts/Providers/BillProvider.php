<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\BillCollection;

/**
 * Lists/browses bills for the active state context.
 *
 * This is the mandatory floor for bill access — every driver that exposes
 * bills at all can implement it (an RSS feed can list what it publishes).
 */
interface BillProvider
{
    public function bills(): BillCollection;
}
