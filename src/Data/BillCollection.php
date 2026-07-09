<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * @extends Collection<int, Bill>
 */
class BillCollection extends Collection
{
    public function byChamber(Chamber $chamber): static
    {
        return $this->filter(fn (Bill $bill) => $bill->chamber === $chamber);
    }

    /**
     * Bills whose most recent action falls within the last $days days.
     */
    public function recentlyActed(int $days = 30): static
    {
        $threshold = now()->subDays($days);

        return $this->filter(
            fn (Bill $bill) => $bill->lastActionDate !== null
                && $bill->lastActionDate->greaterThanOrEqualTo($threshold)
        );
    }
}
