<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;

/**
 * A bill's referral to a committee, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   committee_id  int|string   a stable identifier for the committee; sources
 *                              with no numeric id of their own may synthesize
 *                              one, as long as it is stable across referrals
 *   name          string       the committee's name
 *   chamber       string|null  the source's own chamber code (e.g. "H"/"S"),
 *                              left unresolved -- see {@see BillHistoryEntry}
 *   date          string|CarbonInterface|null
 */
final class CommitteeReferral extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $committeeId;

    #[Computed]
    public string $name;

    #[Computed]
    public ?string $chamber;

    #[Computed]
    public ?CarbonInterface $date;

    public function __construct(public array $meta)
    {
        $this->committeeId = $this->meta['committee_id'] ?? 0;
        $this->name = self::parseString($this->meta['name'] ?? '');
        $chamber = $this->meta['chamber'] ?? null;
        $this->chamber = is_string($chamber) && $chamber !== '' ? $chamber : null;
        $this->date = self::parseDate($this->meta['date'] ?? null);
    }
}
