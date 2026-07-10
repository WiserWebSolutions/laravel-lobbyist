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
    public function sessions(): SessionCollection
    {
        return new SessionCollection;
    }

    public function bills(): BillCollection
    {
        return new BillCollection;
    }

    public function bill(string|int $identifier): Bill
    {
        return new Bill(meta: ['id' => $identifier]);
    }

    public function votes(): VoteCollection
    {
        return new VoteCollection;
    }

    public function vote(string|int $identifier): Vote
    {
        return new Vote(meta: ['id' => $identifier]);
    }

    public function representatives(): LegislatorCollection
    {
        return new LegislatorCollection;
    }

    public function representative(string|int $identifier): Legislator
    {
        return new Legislator(meta: ['id' => $identifier]);
    }
}
