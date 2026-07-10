<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;

/**
 * Lists/browses elected representatives for the active state context.
 */
interface RepresentativeProvider
{
    public function representatives(): LegislatorCollection;
}
