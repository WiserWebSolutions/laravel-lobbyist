<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * @extends Collection<int, CommitteeAssignment>
 */
class CommitteeAssignmentCollection extends Collection
{
    public function byChamber(Chamber $chamber): static
    {
        return $this->filter(fn (CommitteeAssignment $seat) => $seat->chamber === $chamber);
    }

    public function forCommittee(string $committee): static
    {
        return $this->filter(
            fn (CommitteeAssignment $seat) => strcasecmp($seat->committee, $committee) === 0
        );
    }

    /**
     * Full committees only, leaving subcommittee seats out.
     */
    public function committeesOnly(): static
    {
        return $this->reject(fn (CommitteeAssignment $seat) => $seat->isSubcommittee());
    }

    public function chairs(): static
    {
        return $this->filter(fn (CommitteeAssignment $seat) => $seat->isChair());
    }

    public function viceChairs(): static
    {
        return $this->filter(fn (CommitteeAssignment $seat) => $seat->isViceChair());
    }

    /**
     * Seats grouped by the committee they belong to.
     *
     * @return Collection<string, static>
     */
    public function groupedByCommittee(): Collection
    {
        return $this->groupBy(fn (CommitteeAssignment $seat) => $seat->committee);
    }

    /**
     * Every distinct committee named, in the order first seen.
     *
     * @return Collection<int, string>
     */
    public function committeeNames(): Collection
    {
        return $this->map(fn (CommitteeAssignment $seat) => $seat->committee)->unique()->values();
    }
}
