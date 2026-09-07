<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * @extends Collection<int, ChamberSessionDay>
 */
class ChamberSessionDayCollection extends Collection
{
    public function byChamber(Chamber $chamber): static
    {
        return $this->filter(fn (ChamberSessionDay $day) => $day->chamber === $chamber);
    }

    public function upcoming(): static
    {
        return $this->filter(fn (ChamberSessionDay $day) => $day->isUpcoming())
            ->sortBy(fn (ChamberSessionDay $day) => $day->date?->getTimestamp() ?? PHP_INT_MAX)
            ->values();
    }

    /** Days the chamber actually voted, excluding non-voting session days. */
    public function votingDays(): static
    {
        return $this->filter(fn (ChamberSessionDay $day) => $day->votingDay)->values();
    }
}
