<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillText;
use WiserWebSolutions\Lobbyist\Data\BillTextCollection;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\Session;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

/**
 * Core DTOs are provider-agnostic: they derive typed properties from a
 * normalized `meta` shape. Provider-specific mapping is tested in each driver
 * package's mapper tests.
 */
class DtoMappingTest extends TestCase
{
    public function test_session_derives_from_normalized_meta(): void
    {
        $session = new Session(meta: [
            'id' => 1791,
            'state' => StateEnum::CA,
            'title' => '2021-2022 Regular Session',
            'name' => '2021-2022 Session',
        ]);

        $this->assertSame(1791, $session->id);
        $this->assertSame(StateEnum::CA, $session->state);
        $this->assertSame('2021-2022 Regular Session', $session->title);
    }

    public function test_bill_derives_from_normalized_meta(): void
    {
        $bill = new Bill(meta: [
            'id' => 1132030,
            'number' => 'AB1',
            'title' => 'Youth athletics',
            'state' => StateEnum::CA,
            'last_action_date' => '2018-12-04',
        ]);

        $this->assertSame(1132030, $bill->id);
        $this->assertSame('AB1', $bill->number);
        $this->assertSame(StateEnum::CA, $bill->state);
        $this->assertSame(Chamber::House, $bill->chamber); // inferred from "A" prefix
        $this->assertSame('2018-12-04', $bill->lastActionDate?->format('Y-m-d'));
    }

    public function test_bill_is_null_safe_on_empty_meta(): void
    {
        $bill = new Bill(meta: []);

        $this->assertSame(0, $bill->id);
        $this->assertSame('', $bill->number);
        $this->assertSame(StateEnum::US, $bill->state);
        $this->assertNull($bill->statusDate);
        $this->assertNull($bill->lastActionDate);
    }

    public function test_bill_chamber_prefers_explicit_over_inference(): void
    {
        $bill = new Bill(meta: ['number' => 'HB1', 'chamber' => Chamber::Senate]);

        $this->assertSame(Chamber::Senate, $bill->chamber);
    }

    public function test_vote_derives_from_normalized_meta(): void
    {
        $vote = new Vote(meta: [
            'id' => 55,
            'bill_id' => 1132030,
            'chamber' => Chamber::House,
            'date' => '2019-05-01',
            'description' => 'Third Reading',
            'yea' => 60,
            'nay' => 15,
            'nv' => 2,
            'absent' => 3,
            'passed' => true,
        ]);

        $this->assertSame(55, $vote->id);
        $this->assertSame(1132030, $vote->billId);
        $this->assertSame(Chamber::House, $vote->chamber);
        $this->assertSame(60, $vote->yea);
        $this->assertTrue($vote->passed);
    }

    public function test_legislator_derives_from_normalized_meta(): void
    {
        $legislator = new Legislator(meta: [
            'id' => 9001,
            'name' => 'Jane Doe',
            'party' => Party::Democrat,
            'chamber' => Chamber::House,
            'district' => 'HD-042',
            'state' => StateEnum::PA,
        ]);

        $this->assertSame(9001, $legislator->id);
        $this->assertSame('Jane Doe', $legislator->name);
        $this->assertSame(Party::Democrat, $legislator->party);
        $this->assertSame(Chamber::House, $legislator->chamber);
        $this->assertSame('HD-042', $legislator->district);
        $this->assertSame(StateEnum::PA, $legislator->state);
    }

    public function test_bill_text_derives_from_normalized_meta(): void
    {
        $text = new BillText(meta: [
            'id' => 2029,
            'bill_id' => 1132030,
            'type' => 'Introduced',
            'mime' => 'text/html',
            'date' => '2018-01-05',
            'url' => 'https://legiscan.com/CA/text/AB1/id/2029',
        ]);

        $this->assertSame(2029, $text->id);
        $this->assertSame(1132030, $text->billId);
        $this->assertSame('Introduced', $text->type);
        $this->assertSame('2018-01-05', $text->date?->format('Y-m-d'));
        $this->assertNull($text->content);
    }

    public function test_bill_text_is_null_safe_on_empty_meta(): void
    {
        $text = new BillText(meta: []);

        $this->assertSame(0, $text->id);
        $this->assertNull($text->billId);
        $this->assertSame('', $text->type);
        $this->assertNull($text->date);
    }

    public function test_bill_text_collection_latest_prefers_the_most_recent_date(): void
    {
        $texts = new BillTextCollection([
            new BillText(meta: ['id' => 1, 'type' => 'Introduced', 'date' => '2024-01-01']),
            new BillText(meta: ['id' => 2, 'type' => 'Enrolled', 'date' => '2024-03-01']),
            new BillText(meta: ['id' => 3, 'type' => 'Amended', 'date' => '2024-02-01']),
        ]);

        $this->assertSame(2, $texts->latest()->id);
    }

    public function test_bill_text_collection_latest_falls_back_to_the_last_entry_without_dates(): void
    {
        $texts = new BillTextCollection([
            new BillText(meta: ['id' => 1, 'type' => 'Printer 1']),
            new BillText(meta: ['id' => 2, 'type' => 'Printer 2']),
        ]);

        $this->assertSame(2, $texts->latest()->id);
    }

    public function test_bill_text_collection_latest_is_null_when_empty(): void
    {
        $this->assertNull((new BillTextCollection([]))->latest());
    }

    public function test_string_enums_are_resolved_by_constructor(): void
    {
        // A driver may pass a raw string; the DTO coerces it via the enum.
        $legislator = new Legislator(meta: ['party' => 'R', 'chamber' => 'senate']);

        $this->assertSame(Party::Republican, $legislator->party);
        $this->assertSame(Chamber::Senate, $legislator->chamber);
    }
}
