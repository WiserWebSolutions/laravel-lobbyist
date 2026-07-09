<?php

namespace WiserWebSolutions\Lobbyist\Tests;

use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;
use WiserWebSolutions\Lobbyist\Facades\Lobbyist;
use WiserWebSolutions\Lobbyist\LobbyistManager;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeFullDriver;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeListOnlyDriver;

class LobbyistManagerTest extends TestCase
{
    private function manager(): LobbyistManager
    {
        return $this->app->make('lobbyist');
    }

    public function test_state_resolves_a_registered_state_driver(): void
    {
        $this->manager()->extend('pa', fn () => new FakeListOnlyDriver);

        $driver = Lobbyist::state('PA');

        $this->assertInstanceOf(FakeListOnlyDriver::class, $driver);
    }

    public function test_state_falls_back_to_the_default_driver_when_unregistered(): void
    {
        $this->manager()->extend('legiscan', fn () => new FakeFullDriver);

        $driver = Lobbyist::state('TX');

        $this->assertInstanceOf(FakeFullDriver::class, $driver);
    }

    public function test_state_context_receives_the_original_state_string(): void
    {
        $this->manager()->extend('legiscan', fn () => new FakeFullDriver);

        $driver = Lobbyist::state('tx');

        // AbstractDriver normalizes to upper-case.
        $this->assertSame('TX', $driver->stateContext());
    }

    public function test_missing_default_driver_throws_a_helpful_exception(): void
    {
        $this->expectException(LobbyistException::class);
        $this->expectExceptionMessageMatches('/No Lobbyist driver \[legiscan\] is registered/');

        Lobbyist::state('TX');
    }

    public function test_default_driver_name_comes_from_config(): void
    {
        config()->set('lobbyist.drivers.default', 'legiscan');

        $this->assertSame('legiscan', $this->manager()->getDefaultDriver());
    }
}
