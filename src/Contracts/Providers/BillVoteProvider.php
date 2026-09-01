<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\VoteCollection;

/**
 * Lists the roll calls taken on one specific bill.
 *
 * This exists alongside {@see VoteProvider} because the two describe genuinely
 * different source shapes, and most APIs can only satisfy one of them. A feed
 * that publishes "recent votes" chamber-wide fits `VoteProvider`; an API whose
 * roll calls are only reachable through a bill fits this one. A driver that
 * cannot answer "every vote in the state" is therefore not necessarily unable
 * to answer "every vote on this bill" — usually the cheaper and more useful
 * question, and often answerable from a bill payload already in hand.
 */
interface BillVoteProvider
{
    public function votesForBill(string|int $identifier): VoteCollection;
}
