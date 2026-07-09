<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Support\Collection;

/**
 * @extends Collection<int, Session>
 */
class SessionCollection extends Collection
{
    public function active(): static
    {
        return $this->filter(fn (Session $s) => !($s->meta['prior'] ?? false) && !($s->meta['sine_die'] ?? false));
    }

    public function special(): static
    {
        return $this->filter(fn (Session $s) => (bool) ($s->meta['special'] ?? false));
    }
}