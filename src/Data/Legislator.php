<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;

/**
 * A normalized elected representative / legislator, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   id           int|string
 *   name         string
 *   first_name   string
 *   last_name    string
 *   party        Party|string|null
 *   chamber      Chamber|string|null
 *   district     string|null
 *   role         string|null
 *   state        StateEnum
 *   active       bool|null
 *   url          string
 */
final class Legislator extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $id;

    #[Computed]
    public string $name;

    #[Computed]
    public string $firstName;

    #[Computed]
    public string $lastName;

    #[Computed]
    public Party $party;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public ?string $district;

    #[Computed]
    public ?string $role;

    #[Computed]
    public StateEnum $state;

    #[Computed]
    public ?bool $active;

    #[Computed]
    public string $url;

    public function __construct(public array $meta)
    {
        $this->id = $this->meta['id'] ?? 0;
        $this->name = self::parseString($this->meta['name'] ?? '');
        $this->firstName = self::parseString($this->meta['first_name'] ?? '');
        $this->lastName = self::parseString($this->meta['last_name'] ?? '');
        $this->party = ($this->meta['party'] ?? null) instanceof Party
            ? $this->meta['party']
            : Party::fromString($this->meta['party'] ?? null);
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromString($this->meta['chamber'] ?? null);
        $this->district = isset($this->meta['district']) ? (string) $this->meta['district'] : null;
        $this->role = isset($this->meta['role']) ? (string) $this->meta['role'] : null;
        $this->state = ($this->meta['state'] ?? null) instanceof StateEnum
            ? $this->meta['state']
            : StateEnum::US;
        $this->active = isset($this->meta['active']) ? (bool) $this->meta['active'] : null;
        $this->url = self::parseString($this->meta['url'] ?? '');
    }
}
