<?php

namespace WiserWebSolutions\Lobbyist\Contracts;

use Illuminate\Support\LazyCollection;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\Dataset;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\Vote;

/**
 * One legislative session's bills, votes and people, opened for reading.
 *
 * Every accessor returns a {@see LazyCollection} rather than a materialized
 * one, and that is the whole point of this contract. A single session can
 * hold thousands of bills and roll calls; returning a BillCollection of all
 * of them would load the entire session into memory at once. Consumers are
 * expected to iterate and persist in chunks.
 *
 * Iteration is repeatable: reading bills and then votes re-reads the source
 * rather than consuming it, so one archive instance serves every record type.
 *
 * This is agnostic to how the underlying driver actually fetches the data --
 * a single downloaded ZIP, several independent feeds, or a mix of both are
 * all valid implementations, as long as each accessor above holds. Whatever
 * the archive occupies (disk space, a temp download, cached HTTP responses)
 * is owned by the consumer's use of it, and should be released via
 * {@see delete()} when finished.
 */
interface DatasetArchive
{
    /**
     * The metadata for the archive this was opened from.
     */
    public function dataset(): Dataset;

    /**
     * Where the archive is stored locally, if it corresponds to a single
     * downloaded file. A driver assembling this from multiple live sources
     * (feeds, scraped pages) may return a synthetic or representative path
     * -- callers that need a real file on disk should check {@see self::dataset()}
     * or the driver's own documentation rather than assume one.
     */
    public function path(): string;

    /**
     * @return LazyCollection<int, Bill>
     */
    public function bills(): LazyCollection;

    /**
     * Roll calls, ideally each carrying its per-member positions -- a bulk
     * archive typically has this for free, where a live API would charge one
     * request per roll call. A source that cannot supply positions may still
     * satisfy this contract with tally-only votes; see
     * {@see Vote::positions()}.
     *
     * @return LazyCollection<int, Vote>
     */
    public function votes(): LazyCollection;

    /**
     * @return LazyCollection<int, Legislator>
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
