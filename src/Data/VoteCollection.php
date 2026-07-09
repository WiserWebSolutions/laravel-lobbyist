<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * @extends Collection<int, Vote>
 */
class VoteCollection extends Collection
{
    public function byChamber(Chamber $chamber): static
    {
        return $this->filter(fn (Vote $vote) => $vote->chamber === $chamber);
    }

    public function passed(): static
    {
        return $this->filter(fn (Vote $vote) => $vote->passed === true);
    }
}
