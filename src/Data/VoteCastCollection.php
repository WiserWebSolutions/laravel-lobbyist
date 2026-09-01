<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\VotePosition;

/**
 * @extends Collection<int, VoteCast>
 */
class VoteCastCollection extends Collection
{
    public function withPosition(VotePosition $position): static
    {
        return $this->filter(fn (VoteCast $cast) => $cast->position === $position);
    }

    public function yeas(): static
    {
        return $this->withPosition(VotePosition::Yea);
    }

    public function nays(): static
    {
        return $this->withPosition(VotePosition::Nay);
    }

    /**
     * The cast made by a given legislator, if that member is recorded.
     */
    public function forLegislator(int|string $legislatorId): ?VoteCast
    {
        return $this->first(
            fn (VoteCast $cast) => (string) $cast->legislatorId === (string) $legislatorId
        );
    }
}
