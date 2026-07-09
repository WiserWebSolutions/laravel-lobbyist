<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;

/**
 * A normalized legislative session, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   id       int
 *   name     string
 *   title    string
 *   state    StateEnum
 *   prior    bool    used by SessionCollection::active()
 *   sine_die bool    used by SessionCollection::active()
 *   special  bool    used by SessionCollection::special()
 */
final class Session extends Data
{
    #[Computed]
    public int $id;

    #[Computed]
    public string $name;

    #[Computed]
    public string $title;

    #[Computed]
    public StateEnum $state;

    public function __construct(public array $meta)
    {
        $this->id = (int) ($this->meta['id'] ?? 0);
        $this->title = (string) ($this->meta['title'] ?? '');
        $this->name = (string) ($this->meta['name'] ?? '');
        $this->state = ($this->meta['state'] ?? null) instanceof StateEnum
            ? $this->meta['state']
            : StateEnum::US;
    }
}
