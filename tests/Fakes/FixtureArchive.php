<?php

namespace WiserWebSolutions\Lobbyist\Tests\Fakes;

use ZipArchive;

/**
 * Builds a small dataset archive on disk, laid out the way real bulk archives
 * are.
 *
 * The structure here is not invented: it mirrors an actual LegiScan session
 * archive, down to the per-record envelope key and the non-record files that
 * sit at the archive root. Those root files are the reason
 * {@see \WiserWebSolutions\Lobbyist\Support\ZipDatasetArchive} selects entries
 * rather than assuming everything in the ZIP is a record.
 */
class FixtureArchive
{
    public const SESSION_DIRECTORY = 'PA/2025-2026_Regular_Session';

    /**
     * @param  array<string, array>  $bills   filename stem => bill payload
     * @param  array<string, array>  $votes   filename stem => roll call payload
     * @param  array<string, array>  $people  filename stem => person payload
     */
    public static function make(array $bills = [], array $votes = [], array $people = []): string
    {
        $path = tempnam(sys_get_temp_dir(), 'lobbyist-fixture-').'.zip';

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Real archives ship these alongside the records.
        $zip->addFromString(self::SESSION_DIRECTORY.'/LICENSE', 'BSD-3-Clause');
        $zip->addFromString(self::SESSION_DIRECTORY.'/README.md', '# Dataset');
        $zip->addFromString(self::SESSION_DIRECTORY.'/hash.md5', 'd41d8cd98f00b204e9800998ecf8427e');

        foreach ($bills as $name => $payload) {
            $zip->addFromString(
                self::SESSION_DIRECTORY.'/bill/'.$name.'.json',
                (string) json_encode(['bill' => $payload])
            );
        }

        foreach ($votes as $name => $payload) {
            $zip->addFromString(
                self::SESSION_DIRECTORY.'/vote/'.$name.'.json',
                (string) json_encode(['roll_call' => $payload])
            );
        }

        foreach ($people as $name => $payload) {
            $zip->addFromString(
                self::SESSION_DIRECTORY.'/people/'.$name.'.json',
                (string) json_encode(['person' => $payload])
            );
        }

        $zip->close();

        return $path;
    }

    /**
     * A minimal archive with one record of each type.
     */
    public static function minimal(): string
    {
        return self::make(
            bills: ['HB1' => ['bill_id' => 1, 'bill_number' => 'HB1', 'change_hash' => 'aaa']],
            votes: ['991' => ['roll_call_id' => 991, 'bill_id' => 1, 'yea' => 1, 'nay' => 0, 'votes' => [
                ['people_id' => 501, 'vote_id' => 1, 'vote_text' => 'Yea'],
            ]]],
            people: ['501' => ['people_id' => 501, 'name' => 'Rep. Fixture', 'role' => 'Rep']],
        );
    }
}
