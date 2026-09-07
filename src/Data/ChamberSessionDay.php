<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * One day a chamber is (or was) in session — distinct from
 * {@see CommitteeMeeting}, which is one committee's own meeting, and from
 * anything bill-scoped: this is "was/will the chamber itself convene", the
 * question neither a bill list nor a committee schedule answers.
 *
 * Deliberately carries no time. Sources that publish a session-day calendar
 * (a list of dates the chamber convened or will convene) generally do not
 * attach a time to each historical entry — only to the next upcoming one,
 * and even then as a live, separately-published banner rather than a
 * per-day field. Inventing one would claim a precision the source doesn't
 * have.
 */
final class ChamberSessionDay extends Data
{
    use ParsesValues;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public ?CarbonInterface $date;

    /**
     * Whether the chamber took floor action (votes, amendments) this day, as
     * opposed to a non-voting session day held only to preserve continuity
     * or meet a quorum requirement. Sources that mark this distinction do so
     * per day, not inferred after the fact from vote counts.
     */
    #[Computed]
    public bool $votingDay;

    /** A stable identity for the day, for callers that store them. */
    #[Computed]
    public string $identifier;

    #[Computed]
    public string $url;

    public function __construct(public array $meta)
    {
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromString($this->meta['chamber'] ?? null);
        $this->date = self::parseDate($this->meta['date'] ?? null);
        $this->votingDay = (bool) ($this->meta['voting_day'] ?? true);
        $this->identifier = self::parseString($this->meta['identifier'] ?? '');
        $this->url = self::parseString($this->meta['url'] ?? '');
    }

    public function isUpcoming(): bool
    {
        return $this->date !== null && ! $this->date->isBefore(now()->startOfDay());
    }
}
