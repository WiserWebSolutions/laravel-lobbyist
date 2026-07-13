<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;

/**
 * Lists a state's legislators for the active state context.
 *
 * This is the mandatory floor for legislator access — every driver that
 * exposes legislators at all can implement it.
 */
interface LegislatorProvider
{
    /**
     * All members of the upper chamber (Senate).
     */
    public function senators(): LegislatorCollection;

    /**
     * All members of the lower chamber (House).
     */
    public function representatives(): LegislatorCollection;

    /**
     * Every legislator, both chambers combined.
     */
    public function legislators(): LegislatorCollection;
}
