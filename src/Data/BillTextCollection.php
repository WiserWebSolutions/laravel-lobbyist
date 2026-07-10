<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, BillText>
 */
class BillTextCollection extends Collection
{
    /**
     * The most recent version by date, falling back to the last entry when
     * dates are unavailable (some feed-only sources don't expose one per
     * version) — the collection's own ordering is assumed to be chronological.
     */
    public function latest(): ?BillText
    {
        return $this
            ->sortBy(fn (BillText $text) => $text->date?->getTimestamp() ?? PHP_INT_MIN)
            ->last();
    }
}
