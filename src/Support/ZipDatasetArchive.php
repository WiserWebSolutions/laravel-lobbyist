<?php

namespace WiserWebSolutions\Lobbyist\Support;

use Generator;
use Illuminate\Support\LazyCollection;
use WiserWebSolutions\Lobbyist\Contracts\DatasetArchive;
use WiserWebSolutions\Lobbyist\Data\Dataset;
use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;
use ZipArchive;

/**
 * A {@see DatasetArchive} over a ZIP of one-record-per-file JSON entries.
 *
 * Bulk archives commonly take this shape: a directory per record type, holding
 * one JSON file per bill, roll call or member. Nothing here knows any source's
 * field names — the caller supplies a mapper per record type, so a driver can
 * reuse whatever mapping it already applies to live API responses and the two
 * paths cannot drift.
 *
 * Entries are read one at a time and never accumulated. A session archive can
 * hold five thousand bills and as many roll calls; decoding them all at once
 * would defeat the purpose of streaming.
 */
class ZipDatasetArchive implements DatasetArchive
{
    /**
     * @param  array{bill: callable(array): mixed, vote: callable(array): mixed, people: callable(array): mixed}  $mappers
     *                                                                                                                      Maps one decoded record payload to a DTO, per record type.
     * @param  array{bill: string, vote: string, people: string}  $directories
     *                                                                        The archive subdirectory holding each record type.
     */
    public function __construct(
        private readonly Dataset $dataset,
        private readonly string $path,
        private readonly array $mappers,
        private readonly array $directories = ['bill' => 'bill', 'vote' => 'vote', 'people' => 'people'],
    ) {}

    public function dataset(): Dataset
    {
        return $this->dataset;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function bills(): LazyCollection
    {
        return $this->records('bill');
    }

    public function votes(): LazyCollection
    {
        return $this->records('vote');
    }

    public function people(): LazyCollection
    {
        return $this->records('people');
    }

    public function counts(): array
    {
        $counts = ['bills' => 0, 'votes' => 0, 'people' => 0];
        $zip = $this->open();

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);

                if (! $this->isJsonEntry($name)) {
                    continue;
                }

                match (true) {
                    $this->isInDirectory($name, $this->directories['bill']) => $counts['bills']++,
                    $this->isInDirectory($name, $this->directories['vote']) => $counts['votes']++,
                    $this->isInDirectory($name, $this->directories['people']) => $counts['people']++,
                    default => null,
                };
            }
        } finally {
            $zip->close();
        }

        return $counts;
    }

    public function delete(): void
    {
        if (is_file($this->path)) {
            @unlink($this->path);
        }
    }

    /**
     * A repeatable lazy stream over one record type.
     *
     * The generator opens its own handle, so reading bills and then votes walks
     * the archive twice instead of consuming a shared cursor.
     */
    private function records(string $type): LazyCollection
    {
        return LazyCollection::make(fn (): Generator => $this->walk($type));
    }

    private function walk(string $type): Generator
    {
        $directory = $this->directories[$type];
        $mapper = $this->mappers[$type];
        $zip = $this->open();

        try {
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $name = (string) $zip->getNameIndex($i);

                if (! $this->isJsonEntry($name) || ! $this->isInDirectory($name, $directory)) {
                    continue;
                }

                $payload = $this->decode($zip->getFromIndex($i));

                if ($payload === null) {
                    continue;
                }

                yield $mapper($payload);
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * Decode one entry, unwrapping a single-key envelope when present.
     *
     * Archive entries typically mirror the API response for the same record,
     * wrapping the payload under one key (`{"bill": {...}}`). Unwrapping here is
     * what lets a driver reuse its live-response mappers verbatim. A payload
     * that is already bare has many keys and passes through untouched.
     */
    private function decode(string|false $contents): ?array
    {
        if ($contents === false || $contents === '') {
            return null;
        }

        $decoded = json_decode($contents, true);

        if (! is_array($decoded) || $decoded === []) {
            return null;
        }

        if (count($decoded) === 1) {
            $inner = reset($decoded);

            if (is_array($inner)) {
                return $inner;
            }
        }

        return $decoded;
    }

    /**
     * Archives carry non-record files at the root (licences, checksums,
     * readmes), so entries are selected rather than assumed.
     */
    private function isJsonEntry(string $name): bool
    {
        return ! str_ends_with($name, '/') && str_ends_with(strtolower($name), '.json');
    }

    /**
     * Matched on the entry's immediate parent directory, because the path above
     * it encodes state and session names that vary per archive.
     */
    private function isInDirectory(string $name, string $directory): bool
    {
        $parent = str_replace('\\', '/', dirname($name));

        return $parent === $directory || str_ends_with($parent, '/'.$directory);
    }

    private function open(): ZipArchive
    {
        if (! is_file($this->path)) {
            throw LobbyistException::driverError("Dataset archive is missing at [{$this->path}].");
        }

        $zip = new ZipArchive();
        $opened = $zip->open($this->path);

        if ($opened !== true) {
            throw LobbyistException::driverError(
                "Could not open dataset archive at [{$this->path}] (code {$opened})."
            );
        }

        return $zip;
    }
}
