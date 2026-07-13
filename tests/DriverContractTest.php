<?php

namespace WiserWebSolutions\Lobbyist\Tests;

use WiserWebSolutions\Lobbyist\Contracts\Capability;
use WiserWebSolutions\Lobbyist\Testing\AssertsDriverContract;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeFullDriver;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeListOnlyDriver;

class DriverContractTest extends TestCase
{
    use AssertsDriverContract;

    public function test_full_driver_advertises_every_capability(): void
    {
        $driver = new FakeFullDriver;

        $this->assertDriverContract($driver);
        $this->assertTrue($driver->supports(Capability::GetBill));
        $this->assertTrue($driver->supports(Capability::GetBillText));
        $this->assertTrue($driver->supports(Capability::ListBillTextHistory));
        $this->assertCount(9, $driver->capabilities());
    }

    public function test_full_driver_splits_legislators_by_chamber(): void
    {
        $driver = new FakeFullDriver;

        $this->assertCount(11, $driver->legislators());
        $this->assertCount(5, $driver->representatives());
        $this->assertCount(6, $driver->senators());
    }

    public function test_list_only_driver_advertises_only_list_capabilities(): void
    {
        $driver = new FakeListOnlyDriver;

        $this->assertDriverContract($driver);

        $this->assertTrue($driver->supports(Capability::ListBills));
        $this->assertTrue($driver->supports(Capability::ListVotes));
        $this->assertFalse($driver->supports(Capability::GetBill));
        $this->assertFalse($driver->supports(Capability::ListLegislators));
    }

    public function test_unsupported_lookup_throws_rather_than_fatals(): void
    {
        $driver = new FakeListOnlyDriver;

        $this->assertUnsupportedLookupThrows($driver, 'bill');
        $this->assertUnsupportedLookupThrows($driver, 'vote');
        $this->assertUnsupportedLookupThrows($driver, 'representative');
        $this->assertUnsupportedLookupThrows($driver, 'billText');
        $this->assertUnsupportedLookupThrows($driver, 'billTextHistory');
    }
}
