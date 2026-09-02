<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;

/**
 * One legislator's seat on one committee.
 *
 * The unit is the seat rather than the committee, because that is the shape
 * sources publish: a roster arrives as a list of members each naming their
 * committees, not as a committee naming its members. Callers that want the
 * latter group by {@see self::committee}.
 *
 * `position` is free text from the source -- "Chair", "Vice Chair",
 * "Secretary", or empty for a rank-and-file seat -- and is deliberately not an
 * enum. Chambers invent titles, and a driver should not have to drop a real one
 * because this package had not heard of it.
 */
final class CommitteeAssignment extends Data
{
    use ParsesValues;

    #[Computed]
    public string $committee;

    #[Computed]
    public ?Chamber $chamber;

    /** The source's own id for the member, where it publishes one. */
    #[Computed]
    public int|string|null $legislatorId;

    #[Computed]
    public string $legislatorName;

    #[Computed]
    public ?string $district;

    #[Computed]
    public Party $party;

    /** "Chair", "Vice Chair", "Secretary", or null for an ordinary seat. */
    #[Computed]
    public ?string $position;

    /**
     * The parent committee, when this seat is on a subcommittee.
     */
    #[Computed]
    public ?string $parentCommittee;

    public function __construct(public array $meta)
    {
        $this->committee = self::parseString($this->meta['committee'] ?? '');
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromString($this->meta['chamber'] ?? null);
        $this->legislatorId = $this->meta['legislator_id'] ?? null;
        $this->legislatorName = self::parseString($this->meta['legislator_name'] ?? '');
        $this->district = isset($this->meta['district']) && (string) $this->meta['district'] !== ''
            ? (string) $this->meta['district']
            : null;
        $this->party = ($this->meta['party'] ?? null) instanceof Party
            ? $this->meta['party']
            : Party::fromString($this->meta['party'] ?? null);

        $position = isset($this->meta['position']) ? trim((string) $this->meta['position']) : '';
        $this->position = $position === '' ? null : $position;

        $parent = isset($this->meta['parent_committee']) ? trim((string) $this->meta['parent_committee']) : '';
        $this->parentCommittee = $parent === '' ? null : $parent;
    }

    public function isChair(): bool
    {
        return $this->position !== null && str_contains(strtolower($this->position), 'chair')
            && ! $this->isViceChair();
    }

    public function isViceChair(): bool
    {
        return $this->position !== null && str_contains(strtolower($this->position), 'vice');
    }

    public function isSubcommittee(): bool
    {
        return $this->parentCommittee !== null;
    }
}
