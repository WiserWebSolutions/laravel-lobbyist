<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillHistoryEntry;
use WiserWebSolutions\Lobbyist\Data\BillHistoryEntryCollection;
use WiserWebSolutions\Lobbyist\Data\CommitteeReferral;
use WiserWebSolutions\Lobbyist\Data\CommitteeReferralCollection;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Data\VoteCollection;
use WiserWebSolutions\Lobbyist\Enums\SponsorType;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class BillRelationsTest extends TestCase
{
    public function test_change_hash_is_null_when_the_source_does_not_supply_one(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'number' => 'HB1']);

        $this->assertNull($bill->changeHash);
    }

    public function test_change_hash_is_exposed_when_present(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'change_hash' => 'e9d1c8a']);

        $this->assertSame('e9d1c8a', $bill->changeHash);
    }

    public function test_memo_is_null_when_the_source_does_not_supply_one(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'number' => 'HB1']);

        $this->assertNull($bill->memo);
        $this->assertNull($bill->memoUrl);
    }

    public function test_memo_is_exposed_when_present(): void
    {
        $bill = new Bill(meta: [
            'id' => 1,
            'memo' => 'Mandating Cursive Handwriting',
            'memo_url' => 'https://example.test/memo?memoID=43567',
        ]);

        $this->assertSame('Mandating Cursive Handwriting', $bill->memo);
        $this->assertSame('https://example.test/memo?memoID=43567', $bill->memoUrl);
    }

    public function test_a_blank_memo_is_normalized_to_null(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'memo' => '   ', 'memo_url' => '']);

        $this->assertNull($bill->memo);
        $this->assertNull($bill->memoUrl);
    }

    public function test_votes_and_sponsors_default_to_empty_collections(): void
    {
        $bill = new Bill(meta: ['id' => 1]);

        $this->assertInstanceOf(VoteCollection::class, $bill->votes());
        $this->assertInstanceOf(LegislatorCollection::class, $bill->sponsors());
        $this->assertTrue($bill->votes()->isEmpty());
        $this->assertTrue($bill->sponsors()->isEmpty());
    }

    public function test_votes_accepts_a_plain_array_of_votes(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'votes' => [
            new Vote(meta: ['id' => 10, 'yea' => 30, 'nay' => 12, 'passed' => true]),
            new Vote(meta: ['id' => 11, 'yea' => 5, 'nay' => 40, 'passed' => false]),
        ]]);

        $this->assertCount(2, $bill->votes());
        $this->assertSame(10, $bill->votes()->first()->id);
        $this->assertTrue($bill->votes()->first()->passed);
    }

    public function test_votes_passes_through_an_existing_collection(): void
    {
        $collection = new VoteCollection([new Vote(meta: ['id' => 10])]);
        $bill = new Bill(meta: ['id' => 1, 'votes' => $collection]);

        $this->assertSame($collection, $bill->votes());
    }

    public function test_sponsors_retain_their_sponsorship_details(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'sponsors' => [
            new Legislator(meta: [
                'id' => 5,
                'name' => 'Rep. Primary',
                'sponsor_type' => SponsorType::Primary,
                'sponsor_order' => 1,
            ]),
            new Legislator(meta: [
                'id' => 6,
                'name' => 'Rep. Co',
                'sponsor_type' => SponsorType::CoSponsor,
                'sponsor_order' => 2,
            ]),
        ]]);

        $this->assertCount(2, $bill->sponsors());

        // Sponsorship describes the relationship to this bill, not the member,
        // so it lives on meta rather than as a Legislator attribute.
        $this->assertSame(SponsorType::Primary, $bill->sponsors()->first()->meta['sponsor_type']);
        $this->assertSame(1, $bill->sponsors()->first()->meta['sponsor_order']);
        $this->assertSame('Rep. Primary', $bill->sponsors()->first()->name);
    }

    public function test_history_and_referrals_default_to_empty_collections(): void
    {
        $bill = new Bill(meta: ['id' => 1]);

        $this->assertInstanceOf(BillHistoryEntryCollection::class, $bill->history());
        $this->assertInstanceOf(CommitteeReferralCollection::class, $bill->referrals());
        $this->assertTrue($bill->history()->isEmpty());
        $this->assertTrue($bill->referrals()->isEmpty());
    }

    public function test_history_accepts_a_plain_array_of_entries(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'history' => [
            new BillHistoryEntry(meta: ['action' => 'Introduced', 'date' => '2025-01-08']),
            new BillHistoryEntry(meta: ['action' => 'Referred to EDUCATION', 'date' => '2025-01-09']),
        ]]);

        $this->assertCount(2, $bill->history());
        $this->assertSame('Introduced', $bill->history()->first()->action);
    }

    public function test_history_passes_through_an_existing_collection(): void
    {
        $collection = new BillHistoryEntryCollection([new BillHistoryEntry(meta: ['action' => 'Introduced'])]);
        $bill = new Bill(meta: ['id' => 1, 'history' => $collection]);

        $this->assertSame($collection, $bill->history());
    }

    public function test_referrals_accepts_a_plain_array_of_referrals(): void
    {
        $bill = new Bill(meta: ['id' => 1, 'referrals' => [
            new CommitteeReferral(meta: ['committee_id' => 'H:EDUCATION', 'name' => 'Education', 'chamber' => 'H', 'date' => '2025-01-08']),
        ]]);

        $this->assertCount(1, $bill->referrals());
        $this->assertSame('Education', $bill->referrals()->first()->name);
    }

    public function test_referrals_passes_through_an_existing_collection(): void
    {
        $collection = new CommitteeReferralCollection([new CommitteeReferral(meta: ['name' => 'Education'])]);
        $bill = new Bill(meta: ['id' => 1, 'referrals' => $collection]);

        $this->assertSame($collection, $bill->referrals());
    }
}
