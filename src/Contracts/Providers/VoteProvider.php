<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\VoteCollection;

/**
 * Lists/browses votes (roll calls) for the active state context.
 */
interface VoteProvider
{
    public function votes(): VoteCollection;
}
