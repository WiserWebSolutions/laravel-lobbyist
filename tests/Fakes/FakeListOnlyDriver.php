<?php

namespace WiserWebSolutions\Lobbyist\Tests\Fakes;

use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillCollection;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Data\VoteCollection;
use WiserWebSolutions\Lobbyist\Support\AbstractDriver;

/**
 * A driver that can only list bills and votes — stands in for a feed-only
 * driver like palegis that cannot look up arbitrary identifiers.
 */
class FakeListOnlyDriver extends AbstractDriver implements
    BillProvider,
    VoteProvider
{
    public function bills(): BillCollection
    {
        return new BillCollection([
            new Bill(meta: ['id' => 1, 'number' => 'HB1', 'chamber' => 'house']),
            new Bill(meta: ['id' => 2, 'number' => 'SB1', 'chamber' => 'senate']),
        ]);
    }

    public function votes(): VoteCollection
    {
        return new VoteCollection([
            new Vote(meta: ['id' => 1, 'chamber' => 'house']),
            new Vote(meta: ['id' => 2, 'chamber' => 'senate']),
        ]);
    }
}
