<?php

namespace WiserWebSolutions\Lobbyist\Tests\Support;

use Illuminate\Support\LazyCollection;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\Dataset;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;
use WiserWebSolutions\Lobbyist\Support\ZipDatasetArchive;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FixtureArchive;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class ZipDatasetArchiveTest extends TestCase
{
    /** @var list<string> */
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }

        parent::tearDown();
    }

    private function archive(string $path, ?Dataset $dataset = null): ZipDatasetArchive
    {
        $this->paths[] = $path;

        return new ZipDatasetArchive(
            dataset: $dataset ?? new Dataset(meta: ['session_id' => 2192, 'session_name' => '2025-2026']),
            path: $path,
            mappers: [
                'bill' => fn (array $p) => new Bill(meta: ['id' => $p['bill_id'] ?? 0, 'number' => $p['bill_number'] ?? '']),
                'vote' => fn (array $p) => new Vote(meta: ['id' => $p['roll_call_id'] ?? 0]),
                'people' => fn (array $p) => new Legislator(meta: ['id' => $p['people_id'] ?? 0, 'name' => $p['name'] ?? '']),
            ],
        );
    }

    public function test_reads_each_record_type_through_its_mapper(): void
    {
        $archive = $this->archive(FixtureArchive::make(
            bills: [
                'HB1' => ['bill_id' => 1, 'bill_number' => 'HB1'],
                'SB2' => ['bill_id' => 2, 'bill_number' => 'SB2'],
            ],
            votes: ['991' => ['roll_call_id' => 991]],
            people: ['501' => ['people_id' => 501, 'name' => 'Rep. Fixture']],
        ));

        $this->assertSame([1, 2], $archive->bills()->map(fn (Bill $b) => $b->id)->all());
        $this->assertSame([991], $archive->votes()->map(fn (Vote $v) => $v->id)->all());
        $this->assertSame(['Rep. Fixture'], $archive->people()->map(fn (Legislator $l) => $l->name)->all());
    }

    public function test_unwraps_the_single_key_record_envelope(): void
    {
        // Archive entries wrap each record under one key, exactly as the live
        // API responses do. Unwrapping here is what lets a driver reuse its
        // existing response mappers instead of maintaining a second path.
        $archive = $this->archive(FixtureArchive::make(
            bills: ['HB1' => ['bill_id' => 77, 'bill_number' => 'HB77']]
        ));

        $this->assertSame(77, $archive->bills()->first()->id);
        $this->assertSame('HB77', $archive->bills()->first()->number);
    }

    public function test_ignores_the_non_record_files_archives_ship(): void
    {
        // LICENSE, README.md and hash.md5 sit beside the records in a real
        // archive; treating them as records would produce phantom entries.
        $archive = $this->archive(FixtureArchive::make(
            bills: ['HB1' => ['bill_id' => 1, 'bill_number' => 'HB1']]
        ));

        $this->assertCount(1, $archive->bills());
        $this->assertSame(['bills' => 1, 'votes' => 0, 'people' => 0], $archive->counts());
    }

    public function test_streams_rather_than_materializing(): void
    {
        $archive = $this->archive(FixtureArchive::minimal());

        $this->assertInstanceOf(LazyCollection::class, $archive->bills());
        $this->assertInstanceOf(LazyCollection::class, $archive->votes());
        $this->assertInstanceOf(LazyCollection::class, $archive->people());
    }

    public function test_iteration_is_repeatable_so_one_download_serves_every_type(): void
    {
        $archive = $this->archive(FixtureArchive::make(
            bills: ['HB1' => ['bill_id' => 1, 'bill_number' => 'HB1']],
            votes: ['991' => ['roll_call_id' => 991]],
        ));

        // Reading bills must not consume a shared cursor and leave votes empty,
        // nor may a second pass over bills come back short.
        $this->assertCount(1, $archive->bills());
        $this->assertCount(1, $archive->votes());
        $this->assertCount(1, $archive->bills());
    }

    public function test_counts_entries_without_decoding_them(): void
    {
        $archive = $this->archive(FixtureArchive::make(
            bills: ['HB1' => ['bill_id' => 1], 'HB2' => ['bill_id' => 2], 'HB3' => ['bill_id' => 3]],
            votes: ['1' => ['roll_call_id' => 1], '2' => ['roll_call_id' => 2]],
            people: ['9' => ['people_id' => 9]],
        ));

        $this->assertSame(['bills' => 3, 'votes' => 2, 'people' => 1], $archive->counts());
    }

    public function test_skips_entries_that_are_not_valid_json(): void
    {
        $path = FixtureArchive::make(bills: ['HB1' => ['bill_id' => 1]]);
        $this->paths[] = $path;

        $zip = new \ZipArchive();
        $zip->open($path);
        $zip->addFromString(FixtureArchive::SESSION_DIRECTORY.'/bill/broken.json', '{not json');
        $zip->close();

        // A single corrupt entry should cost one record, not the whole import.
        $this->assertCount(1, $this->archive($path)->bills());
    }

    public function test_delete_removes_the_local_copy_and_is_idempotent(): void
    {
        $archive = $this->archive(FixtureArchive::minimal());
        $path = $archive->path();

        $this->assertFileExists($path);

        $archive->delete();
        $this->assertFileDoesNotExist($path);

        $archive->delete();
        $this->assertFileDoesNotExist($path);
    }

    public function test_reading_a_missing_archive_fails_clearly(): void
    {
        $archive = $this->archive(FixtureArchive::minimal());
        $archive->delete();

        $this->expectException(LobbyistException::class);
        $this->expectExceptionMessage('Dataset archive is missing');

        $archive->bills()->all();
    }
}
