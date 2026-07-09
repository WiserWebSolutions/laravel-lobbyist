<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;

/**
 * A normalized legislative bill, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below; core
 * does not know about any specific data source. Recognized keys (all optional):
 *
 *   id                 int|string
 *   number             string   e.g. "HB1234"
 *   title              string
 *   description        string
 *   state              StateEnum
 *   chamber            Chamber|null  (falls back to inference from `number`)
 *   status             string
 *   status_date        string|CarbonInterface|null
 *   last_action        string
 *   last_action_date   string|CarbonInterface|null
 *   url                string
 *   session_id         int|null
 *
 * The raw driver payload may be preserved on `meta` so nothing is lost.
 */
final class Bill extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $id;

    #[Computed]
    public string $number;

    #[Computed]
    public string $title;

    #[Computed]
    public string $description;

    #[Computed]
    public StateEnum $state;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public string $status;

    #[Computed]
    public ?CarbonInterface $statusDate;

    #[Computed]
    public string $lastAction;

    #[Computed]
    public ?CarbonInterface $lastActionDate;

    #[Computed]
    public string $url;

    #[Computed]
    public ?int $sessionId;

    public function __construct(public array $meta)
    {
        $this->id = $this->meta['id'] ?? 0;
        $this->number = self::parseString($this->meta['number'] ?? '');
        $this->title = self::parseString($this->meta['title'] ?? '');
        $this->description = self::parseString($this->meta['description'] ?? '');
        $this->state = ($this->meta['state'] ?? null) instanceof StateEnum
            ? $this->meta['state']
            : StateEnum::US;
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromBillNumber($this->number);
        $this->status = self::parseString($this->meta['status'] ?? '');
        $this->statusDate = self::parseDate($this->meta['status_date'] ?? null);
        $this->lastAction = self::parseString($this->meta['last_action'] ?? '');
        $this->lastActionDate = self::parseDate($this->meta['last_action_date'] ?? null);
        $this->url = self::parseString($this->meta['url'] ?? '');
        $this->sessionId = self::parseIntOrNull($this->meta['session_id'] ?? null);
    }
}
