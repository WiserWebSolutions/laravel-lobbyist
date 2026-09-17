<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, BillHistoryEntry>
 */
class BillHistoryEntryCollection extends Collection
{
    /**
     * The earliest dated entry, used to infer when a bill entered the
     * process. Entries without a date are ignored rather than sorted first.
     */
    public function earliest(): ?BillHistoryEntry
    {
        return $this
            ->filter(fn (BillHistoryEntry $entry) => $entry->date !== null)
            ->sortBy(fn (BillHistoryEntry $entry) => $entry->date->getTimestamp())
            ->first();
    }
}
