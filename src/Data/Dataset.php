<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;

/**
 * A bulk archive of one legislative session, source-agnostic.
 *
 * This describes an archive without downloading it, so a consumer can compare
 * {@see $hash} against a stored copy and skip the transfer entirely when
 * nothing has changed. Drivers map their raw payloads into the normalized
 * `meta` shape below. Recognized keys (all optional):
 *
 *   session_id   int|string
 *   session_name string
 *   state        StateEnum
 *   hash         string|null   revision marker for the archive itself
 *   date         string|CarbonInterface|null   when it was last rebuilt
 *   size         int|null      bytes, before any transport encoding
 *   year_start   int|null
 *   year_end     int|null
 *   access_key   string|null   an opaque token some sources require to fetch
 *
 * The raw driver payload may be preserved on `meta` so nothing is lost.
 */
final class Dataset extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $sessionId;

    #[Computed]
    public string $sessionName;

    #[Computed]
    public StateEnum $state;

    /**
     * A marker for this archive's current revision. Sources rebuild archives on
     * a schedule, so an unchanged hash means a download would return exactly
     * what is already stored.
     */
    #[Computed]
    public ?string $hash;

    #[Computed]
    public ?CarbonInterface $date;

    #[Computed]
    public ?int $size;

    #[Computed]
    public ?int $yearStart;

    #[Computed]
    public ?int $yearEnd;

    /**
     * An opaque token required to fetch this archive, where the source issues
     * one per dataset rather than relying on the API credential alone.
     */
    #[Computed]
    public ?string $accessKey;

    public function __construct(public array $meta)
    {
        $this->sessionId = $this->meta['session_id'] ?? 0;
        $this->sessionName = self::parseString($this->meta['session_name'] ?? '');
        $this->state = ($this->meta['state'] ?? null) instanceof StateEnum
            ? $this->meta['state']
            : StateEnum::US;
        $this->hash = $this->meta['hash'] ?? null;
        $this->date = self::parseDate($this->meta['date'] ?? null);
        $this->size = self::parseIntOrNull($this->meta['size'] ?? null);
        $this->yearStart = self::parseIntOrNull($this->meta['year_start'] ?? null);
        $this->yearEnd = self::parseIntOrNull($this->meta['year_end'] ?? null);
        $this->accessKey = $this->meta['access_key'] ?? null;
    }

    /**
     * Whether this archive differs from a previously stored revision.
     *
     * A null stored hash means nothing has been imported yet, so the archive
     * counts as changed.
     */
    public function hasChangedSince(?string $storedHash): bool
    {
        return $this->hash === null || $this->hash !== $storedHash;
    }
}
