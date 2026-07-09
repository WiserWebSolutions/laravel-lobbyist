<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\SessionCollection;

/**
 * Lists legislative sessions available for the active state context.
 */
interface SessionProvider
{
    public function listSessions(): SessionCollection;
}
