<?php

namespace WiserWebSolutions\Lobbyist\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use WiserWebSolutions\Lobbyist\LobbyistServiceProvider;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LobbyistServiceProvider::class,
        ];
    }
}
