<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\Vote;

/**
 * Fetches a single vote (roll call) by identifier.
 */
interface VoteLookup
{
    public function vote(string|int $identifier): Vote;
}
