<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\Bill;
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
}
