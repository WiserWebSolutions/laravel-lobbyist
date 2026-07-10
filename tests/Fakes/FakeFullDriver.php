<?php

namespace WiserWebSolutions\Lobbyist\Tests\Fakes;

use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillTextHistoryLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillTextLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\SessionProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillCollection;
use WiserWebSolutions\Lobbyist\Data\BillText;
use WiserWebSolutions\Lobbyist\Data\BillTextCollection;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;
use WiserWebSolutions\Lobbyist\Data\Session;
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
    RepresentativeLookup,
    BillTextLookup,
    BillTextHistoryLookup
{
    public function sessions(): SessionCollection
    {
        return new SessionCollection([
            new Session(meta: ['id' => 1, 'name' => 'Old Session', 'prior' => true]),
            new Session(meta: ['id' => 2, 'name' => 'Current Session', 'prior' => false, 'sine_die' => false]),
        ]);
    }

    public function bills(): BillCollection
    {
        return new BillCollection([
            new Bill(meta: ['id' => 1, 'number' => 'HB1', 'chamber' => 'house']),
            new Bill(meta: ['id' => 2, 'number' => 'SB1', 'chamber' => 'senate']),
        ]);
    }

    public function bill(string|int $identifier): Bill
    {
        return new Bill(meta: ['id' => $identifier]);
    }

    public function votes(): VoteCollection
    {
        return new VoteCollection([
            new Vote(meta: ['id' => 1, 'chamber' => 'house']),
            new Vote(meta: ['id' => 2, 'chamber' => 'senate']),
        ]);
    }

    public function vote(string|int $identifier): Vote
    {
        return new Vote(meta: ['id' => $identifier]);
    }

    public function representatives(): LegislatorCollection
    {
        return new LegislatorCollection([
            // House: 3 Democrats, 2 Republicans => 20pt spread => "Slight Democrat".
            new Legislator(meta: ['id' => 1, 'name' => 'House Rep 1', 'chamber' => 'house', 'party' => 'D']),
            new Legislator(meta: ['id' => 2, 'name' => 'House Rep 2', 'chamber' => 'house', 'party' => 'D']),
            new Legislator(meta: ['id' => 3, 'name' => 'House Rep 3', 'chamber' => 'house', 'party' => 'D']),
            new Legislator(meta: ['id' => 4, 'name' => 'House Rep 4', 'chamber' => 'house', 'party' => 'R']),
            new Legislator(meta: ['id' => 5, 'name' => 'House Rep 5', 'chamber' => 'house', 'party' => 'R']),
            // Senate: 1 Democrat, 4 Republicans, 1 Independent => 60pt spread => "Strong Republican".
            new Legislator(meta: ['id' => 6, 'name' => 'Senate Rep 1', 'chamber' => 'senate', 'party' => 'D']),
            new Legislator(meta: ['id' => 7, 'name' => 'Senate Rep 2', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 8, 'name' => 'Senate Rep 3', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 9, 'name' => 'Senate Rep 4', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 10, 'name' => 'Senate Rep 5', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 11, 'name' => 'Senate Rep 6', 'chamber' => 'senate', 'party' => 'I']),
        ]);
    }

    public function representative(string|int $identifier): Legislator
    {
        return new Legislator(meta: ['id' => $identifier]);
    }

    public function billText(string|int $identifier): BillText
    {
        return $this->billTextHistory($identifier)->latest();
    }

    public function billTextHistory(string|int $identifier): BillTextCollection
    {
        return new BillTextCollection([
            new BillText(meta: ['id' => 1, 'bill_id' => $identifier, 'type' => 'Introduced', 'date' => '2024-01-01']),
            new BillText(meta: ['id' => 2, 'bill_id' => $identifier, 'type' => 'Amended', 'date' => '2024-02-01']),
        ]);
    }
}
