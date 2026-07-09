<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;

/**
 * A normalized vote / roll call, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   id           int|string
 *   bill_id      int|null
 *   chamber      Chamber|string|null
 *   date         string|CarbonInterface|null
 *   description  string
 *   yea, nay, nv, absent  int|null
 *   passed       bool|null
 *   url          string
 */
final class Vote extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $id;

    #[Computed]
    public ?int $billId;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public ?CarbonInterface $date;

    #[Computed]
    public string $description;

    #[Computed]
    public ?int $yea;

    #[Computed]
    public ?int $nay;

    #[Computed]
    public ?int $notVoting;

    #[Computed]
    public ?int $absent;

    #[Computed]
    public ?bool $passed;

    #[Computed]
    public string $url;

    public function __construct(public array $meta)
    {
        $this->id = $this->meta['id'] ?? 0;
        $this->billId = self::parseIntOrNull($this->meta['bill_id'] ?? null);
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromString($this->meta['chamber'] ?? null);
        $this->date = self::parseDate($this->meta['date'] ?? null);
        $this->description = self::parseString($this->meta['description'] ?? '');
        $this->yea = self::parseIntOrNull($this->meta['yea'] ?? null);
        $this->nay = self::parseIntOrNull($this->meta['nay'] ?? null);
        $this->notVoting = self::parseIntOrNull($this->meta['nv'] ?? null);
        $this->absent = self::parseIntOrNull($this->meta['absent'] ?? null);
        $this->passed = isset($this->meta['passed']) ? (bool) $this->meta['passed'] : null;
        $this->url = self::parseString($this->meta['url'] ?? '');
    }
}
