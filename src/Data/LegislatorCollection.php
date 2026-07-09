<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;

/**
 * @extends Collection<int, Legislator>
 */
class LegislatorCollection extends Collection
{
    public function byChamber(Chamber $chamber): static
    {
        return $this->filter(fn (Legislator $legislator) => $legislator->chamber === $chamber);
    }

    public function byParty(Party $party): static
    {
        return $this->filter(fn (Legislator $legislator) => $legislator->party === $party);
    }
}
