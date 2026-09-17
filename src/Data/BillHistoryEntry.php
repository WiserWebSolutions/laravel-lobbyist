<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;

/**
 * A single procedural action recorded against a bill (e.g. "Referred to
 * EDUCATION", "Third consideration and final passage"), source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   action      string       the action's description, as the source reports it
 *   date        string|CarbonInterface|null
 *   chamber     string|null  the source's own chamber code (e.g. "H"/"S"),
 *                            left unresolved -- {@see \App\Modules\PolicyPulse\Insights\BillWorkflow::chamberOf()}
 *                            (the one known consumer) normalizes it itself
 *   importance  bool         whether the source flags this as a milestone
 *                            rather than routine housekeeping
 */
final class BillHistoryEntry extends Data
{
    use ParsesValues;

    #[Computed]
    public string $action;

    #[Computed]
    public ?CarbonInterface $date;

    #[Computed]
    public ?string $chamber;

    #[Computed]
    public bool $importance;

    public function __construct(public array $meta)
    {
        $this->action = self::parseString($this->meta['action'] ?? '');
        $this->date = self::parseDate($this->meta['date'] ?? null);
        $chamber = $this->meta['chamber'] ?? null;
        $this->chamber = is_string($chamber) && $chamber !== '' ? $chamber : null;
        $this->importance = (bool) ($this->meta['importance'] ?? false);
    }
}
