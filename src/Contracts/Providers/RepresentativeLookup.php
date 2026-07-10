<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\Legislator;

/**
 * Fetches a single elected representative by identifier.
 */
interface RepresentativeLookup
{
    public function representative(string|int $identifier): Legislator;
}
