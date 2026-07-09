<?php

namespace WiserWebSolutions\Lobbyist\Tests\Fakes;

use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\SessionProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillCollection;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;
use WiserWebSolutions\Lobbyist\Data\SessionCollection;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Data\VoteCollection;
use WiserWebSolutions\Lobbyist\Support\AbstractDriver;

/**
 * A driver that supports every capability — stands in for a rich API driver
 * like LegiScan in core tests.
 */
class FakeFullDriver extends AbstractDriver implements
    SessionProvider,
    BillProvider,
    BillLookup,
    VoteProvider,
    VoteLookup,
    RepresentativeProvider,
    RepresentativeLookup
{
    public function listSessions(): SessionCollection
    {
        return new SessionCollection;
    }

    public function listBills(): BillCollection
    {
        return new BillCollection;
    }

    public function getBill(string|int $identifier): Bill
    {
        return new Bill(meta: ['id' => $identifier]);
    }

    public function listVotes(): VoteCollection
    {
        return new VoteCollection;
    }

    public function getVote(string|int $identifier): Vote
    {
        return new Vote(meta: ['id' => $identifier]);
    }

    public function listRepresentatives(): LegislatorCollection
    {
        return new LegislatorCollection;
    }

    public function getRepresentative(string|int $identifier): Legislator
    {
        return new Legislator(meta: ['id' => $identifier]);
    }
}
