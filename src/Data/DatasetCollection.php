<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Dataset>
 */
class DatasetCollection extends Collection
{
    /**
     * The archive for one session, if this listing includes it.
     */
    public function forSession(int|string $sessionId): ?Dataset
    {
        return $this->first(
            fn (Dataset $dataset) => (string) $dataset->sessionId === (string) $sessionId
        );
    }

    /**
     * The most recently rebuilt archive, which for an ongoing session is also
     * the freshest data available.
     */
    public function latest(): ?Dataset
    {
        return $this
            ->sortBy(fn (Dataset $dataset) => $dataset->date?->getTimestamp() ?? PHP_INT_MIN)
            ->last();
    }

    /**
     * Archives whose revision differs from the stored hashes given, keyed by
     * session id. Sessions absent from the map count as changed, never having
     * been imported.
     *
     * @param  array<int|string, string|null>  $storedHashes
     */
    public function changedSince(array $storedHashes): static
    {
        return $this->filter(
            fn (Dataset $dataset) => $dataset->hasChangedSince($storedHashes[$dataset->sessionId] ?? null)
        );
    }
}
