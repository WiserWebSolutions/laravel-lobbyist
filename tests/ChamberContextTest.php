<?php

namespace WiserWebSolutions\Lobbyist\Tests;

use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Exceptions\UnsupportedOperationException;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeFullDriver;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeListOnlyDriver;

class ChamberContextTest extends TestCase
{
    public function test_chambers_returns_house_then_senate_by_default(): void
    {
        $chambers = (new FakeFullDriver)->chambers();

        $this->assertCount(2, $chambers);
        $this->assertSame(Chamber::House, $chambers->first()->chamber);
        $this->assertSame(Chamber::Senate, $chambers->last()->chamber);
    }

    public function test_chamber_bills_are_filtered_to_the_chamber(): void
    {
        $house = (new FakeFullDriver)->chambers()->first();

        $bills = $house->bills();

        $this->assertCount(1, $bills);
        $this->assertSame('HB1', $bills->first()->number);
    }

    public function test_chamber_votes_are_filtered_to_the_chamber(): void
    {
        $senate = (new FakeFullDriver)->chambers()->last();

        $votes = $senate->votes();

        $this->assertCount(1, $votes);
        $this->assertSame(Chamber::Senate, $votes->first()->chamber);
    }

    public function test_chamber_representatives_are_filtered_to_the_chamber(): void
    {
        $house = (new FakeFullDriver)->chambers()->first();

        $representatives = $house->representatives();

        $this->assertCount(5, $representatives);
        $this->assertSame('House Rep 1', $representatives->first()->name);
    }

    public function test_chamber_scoped_call_throws_when_driver_lacks_the_provider(): void
    {
        $house = (new FakeListOnlyDriver)->chambers()->first();

        $this->expectException(UnsupportedOperationException::class);

        $house->representatives();
    }

    public function test_chamber_lean_reflects_a_slight_majority(): void
    {
        $house = (new FakeFullDriver)->chambers()->first();

        $lean = $house->lean();

        $this->assertSame('Slight Democrat', (string) $lean);
        $this->assertSame('Slight Democrat (3 Democrats, 2 Republicans)', $lean->detail());
    }

    public function test_chamber_lean_reflects_a_strong_majority_and_includes_independents(): void
    {
        $senate = (new FakeFullDriver)->chambers()->last();

        $lean = $senate->lean();

        $this->assertSame('Strong Republican', (string) $lean);
        $this->assertSame('Strong Republican (1 Democrats, 4 Republicans, 1 Independents)', $lean->detail());
    }

    public function test_chamber_lean_throws_when_driver_lacks_representative_provider(): void
    {
        $house = (new FakeListOnlyDriver)->chambers()->first();

        $this->expectException(UnsupportedOperationException::class);

        $house->lean();
    }
}
