<?php

namespace WiserWebSolutions\Lobbyist\Tests\Fakes;

use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\BillCollection;
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
        return new BillCollection;
    }

    public function votes(): VoteCollection
    {
        return new VoteCollection;
    }
}
