<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * @extends Collection<int, CommitteeMeeting>
 */
class CommitteeMeetingCollection extends Collection
{
    public function byChamber(Chamber $chamber): static
    {
        return $this->filter(fn (CommitteeMeeting $meeting) => $meeting->chamber === $chamber);
    }

    public function upcoming(): static
    {
        return $this->filter(fn (CommitteeMeeting $meeting) => $meeting->isUpcoming())
            ->sortBy(fn (CommitteeMeeting $meeting) => $meeting->date?->getTimestamp() ?? PHP_INT_MAX)
            ->values();
    }

    public function forCommittee(string $committee): static
    {
        return $this->filter(
            fn (CommitteeMeeting $meeting) => strcasecmp($meeting->committee, $committee) === 0
        );
    }
}
