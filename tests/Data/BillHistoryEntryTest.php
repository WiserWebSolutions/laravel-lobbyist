<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\BillHistoryEntry;
use WiserWebSolutions\Lobbyist\Data\BillHistoryEntryCollection;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class BillHistoryEntryTest extends TestCase
{
    public function test_maps_the_recognized_fields(): void
    {
        $entry = new BillHistoryEntry(meta: [
            'action' => 'Referred to EDUCATION',
            'date' => '2025-01-08',
            'chamber' => 'H',
            'importance' => true,
        ]);

        $this->assertSame('Referred to EDUCATION', $entry->action);
        $this->assertSame('2025-01-08', $entry->date->toDateString());
        $this->assertSame('H', $entry->chamber);
        $this->assertTrue($entry->importance);
    }

    public function test_chamber_is_left_as_the_sources_own_raw_code(): void
    {
        // Deliberately not resolved to the Chamber enum here -- see the class
        // docblock: the one known consumer normalizes this itself.
        $entry = new BillHistoryEntry(meta: ['chamber' => 'senate']);

        $this->assertSame('senate', $entry->chamber);
    }

    public function test_defaults_when_the_source_omits_fields(): void
    {
        $entry = new BillHistoryEntry(meta: []);

        $this->assertSame('', $entry->action);
        $this->assertNull($entry->date);
        $this->assertNull($entry->chamber);
        $this->assertFalse($entry->importance);
    }

    public function test_earliest_ignores_undated_entries(): void
    {
        $entries = new BillHistoryEntryCollection([
            new BillHistoryEntry(meta: ['action' => 'Third consideration', 'date' => '2025-06-24']),
            new BillHistoryEntry(meta: ['action' => 'No date on this one']),
            new BillHistoryEntry(meta: ['action' => 'Introduced', 'date' => '2025-01-08']),
        ]);

        $this->assertSame('Introduced', $entries->earliest()->action);
    }

    public function test_earliest_is_null_for_an_empty_collection(): void
    {
        $this->assertNull((new BillHistoryEntryCollection())->earliest());
    }
}
