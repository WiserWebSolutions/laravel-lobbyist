<?php

namespace WiserWebSolutions\Lobbyist\Support;

use WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\BillCollection;
use WiserWebSolutions\Lobbyist\Data\Lean;
use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;
use WiserWebSolutions\Lobbyist\Data\VoteCollection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Exceptions\UnsupportedOperationException;

/**
 * A single legislative chamber, scoped to the driver/state it belongs to.
 *
 * Produced by {@see LobbyistDriver::chambers()}; delegates bills/votes/representatives
 * to the owning driver and filters the result to this chamber, throwing
 * {@see UnsupportedOperationException} for operations the driver doesn't back —
 * exactly mirroring the behavior of calling the driver directly.
 */
final class ChamberContext
{
    public function __construct(
        private readonly LobbyistDriver $driver,
        public readonly Chamber $chamber,
    ) {}

    public function bills(): BillCollection
    {
        if (! $this->driver instanceof BillProvider) {
            throw UnsupportedOperationException::for($this->driver, 'bills');
        }

        return $this->driver->bills()->byChamber($this->chamber);
    }

    public function votes(): VoteCollection
    {
        if (! $this->driver instanceof VoteProvider) {
            throw UnsupportedOperationException::for($this->driver, 'votes');
        }

        return $this->driver->votes()->byChamber($this->chamber);
    }

    public function representatives(): LegislatorCollection
    {
        if (! $this->driver instanceof RepresentativeProvider) {
            throw UnsupportedOperationException::for($this->driver, 'representatives');
        }

        return $this->driver->representatives()->byChamber($this->chamber);
    }

    /**
     * The computed political lean of this chamber, based on the party
     * affiliation of its representatives. Throws
     * {@see UnsupportedOperationException} under the same condition as
     * {@see representatives()}, which this delegates to.
     */
    public function lean(): Lean
    {
        return $this->representatives()->lean();
    }
}
