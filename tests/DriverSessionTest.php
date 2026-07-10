<?php

namespace WiserWebSolutions\Lobbyist\Tests;

use WiserWebSolutions\Lobbyist\Contracts\Providers\SessionProvider;
use WiserWebSolutions\Lobbyist\Data\SessionCollection;
use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;
use WiserWebSolutions\Lobbyist\Exceptions\UnsupportedOperationException;
use WiserWebSolutions\Lobbyist\Support\AbstractDriver;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeFullDriver;
use WiserWebSolutions\Lobbyist\Tests\Fakes\FakeListOnlyDriver;

class DriverSessionTest extends TestCase
{
    public function test_session_returns_the_most_recent_active_session(): void
    {
        $session = (new FakeFullDriver)->session();

        $this->assertSame(2, $session->id);
        $this->assertSame('Current Session', $session->name);
    }

    public function test_session_throws_when_driver_lacks_session_provider(): void
    {
        $this->expectException(UnsupportedOperationException::class);

        (new FakeListOnlyDriver)->session();
    }

    public function test_session_throws_a_lobbyist_exception_when_no_sessions_exist(): void
    {
        $driver = new class extends AbstractDriver implements SessionProvider
        {
            public function sessions(): SessionCollection
            {
                return new SessionCollection;
            }
        };

        $this->expectException(LobbyistException::class);
        $this->expectExceptionMessageMatches('/No sessions available/');

        $driver->session();
    }
}
