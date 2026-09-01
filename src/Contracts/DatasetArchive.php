<?php

namespace WiserWebSolutions\Lobbyist\Contracts;

use Illuminate\Support\LazyCollection;
use WiserWebSolutions\Lobbyist\Data\Dataset;

/**
 * A downloaded bulk archive, opened for reading.
 *
 * Every accessor returns a {@see LazyCollection} rather than a materialized
 * one, and that is the whole point of this contract. A single session archive
 * can hold thousands of bills and roll calls; returning a BillCollection of all
 * of them would load the entire session into memory at once. Consumers are
 * expected to iterate and persist in chunks.
 *
 * Iteration is repeatable: reading bills and then votes re-walks the archive
 * rather than consuming it, so one download serves every record type.
 *
 * The archive occupies real disk space, so the consumer owns its lifetime and
 * should call {@see delete()} when finished.
 */
interface DatasetArchive
{
    /**
     * The metadata for the archive this was opened from.
     */
    public function dataset(): Dataset;

    /**
     * Where the archive is stored locally.
     */
    public function path(): string;

    /**
     * @return LazyCollection<int, \WiserWebSolutions\Lobbyist\Data\Bill>
     */
    public function bills(): LazyCollection;

    /**
     * Roll calls, each carrying its per-member positions. In a bulk archive
     * this detail comes for free, where the live API would charge one request
     * per roll call.
     *
     * @return LazyCollection<int, \WiserWebSolutions\Lobbyist\Data\Vote>
     */
    public function votes(): LazyCollection;

    /**
     * @return LazyCollection<int, \WiserWebSolutions\Lobbyist\Data\Legislator>
     */
    public function people(): LazyCollection;

    /**
     * How many entries of each record type the archive holds, without decoding
     * any of them. Useful for progress reporting before a long import.
     *
     * @return array{bills: int, votes: int, people: int}
     */
    public function counts(): array;

    /**
     * Discard the local copy. Safe to call more than once.
     */
    public function delete(): void;
}
