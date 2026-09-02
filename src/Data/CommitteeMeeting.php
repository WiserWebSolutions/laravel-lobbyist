<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * A scheduled public committee meeting.
 *
 * The time stays a string rather than becoming part of the date. Sources
 * publish it as written -- "9:30 AM", "Off the Floor", "At the Call of the
 * Chair" -- and two of those three are not times at all. Parsing would either
 * fail or invent a precision the schedule does not have.
 */
final class CommitteeMeeting extends Data
{
    use ParsesValues;

    #[Computed]
    public string $committee;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public ?CarbonInterface $date;

    /** As published, which is not always a clock time. */
    #[Computed]
    public ?string $time;

    #[Computed]
    public ?string $location;

    #[Computed]
    public ?string $description;

    /** A stable identity for the event, for callers that store them. */
    #[Computed]
    public string $identifier;

    #[Computed]
    public string $url;

    /**
     * Bill numbers on the agenda, where the source lists them.
     *
     * @var array<int, string>
     */
    #[Computed]
    public array $bills;

    public function __construct(public array $meta)
    {
        $this->committee = self::parseString($this->meta['committee'] ?? '');
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromString($this->meta['chamber'] ?? null);
        $this->date = self::parseDate($this->meta['date'] ?? null);

        $time = isset($this->meta['time']) ? trim((string) $this->meta['time']) : '';
        $this->time = $time === '' ? null : $time;

        $location = isset($this->meta['location']) ? trim((string) $this->meta['location']) : '';
        $this->location = $location === '' ? null : $location;

        $description = isset($this->meta['description']) ? trim((string) $this->meta['description']) : '';
        $this->description = $description === '' ? null : $description;

        $this->identifier = self::parseString($this->meta['identifier'] ?? '');
        $this->url = self::parseString($this->meta['url'] ?? '');
        $this->bills = array_values(array_filter(
            array_map(
                fn ($bill): string => trim((string) $bill),
                is_array($this->meta['bills'] ?? null) ? $this->meta['bills'] : []
            ),
            fn (string $bill): bool => $bill !== ''
        ));
    }

    public function isUpcoming(): bool
    {
        return $this->date !== null && ! $this->date->isBefore(now()->startOfDay());
    }
}
