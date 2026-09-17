<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Data\VoteCollection;

/**
 * Lists votes taken inside a committee, as opposed to on the chamber floor.
 *
 * A committee vote is not a new record type -- it is a {@see Vote}
 * whose {@see Vote::$committee} is set. This exists
 * alongside {@see VoteProvider} because most sources that publish floor votes do not
 * also publish committee votes (or vice versa), and a consumer needs to ask for
 * each separately.
 */
interface CommitteeVoteProvider
{
    public function committeeVotes(): VoteCollection;
}
