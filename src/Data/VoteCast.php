<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\VotePosition;

/**
 * One legislator's vote on a single roll call, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   legislator_id  int|string   the source's identifier for the member
 *   position       VotePosition|int|string|null
 *   name           string
 *
 * The aggregate tallies live on {@see Vote}; this is the per-member detail
 * behind them, and is what makes a legislator's voting record reconstructable.
 */
final class VoteCast extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $legislatorId;

    #[Computed]
    public ?VotePosition $position;

    #[Computed]
    public string $name;

    public function __construct(public array $meta)
    {
        $this->legislatorId = $this->meta['legislator_id'] ?? 0;
        $this->position = ($this->meta['position'] ?? null) instanceof VotePosition
            ? $this->meta['position']
            : VotePosition::fromString($this->meta['position'] ?? null);
        $this->name = self::parseString($this->meta['name'] ?? '');
    }
}
