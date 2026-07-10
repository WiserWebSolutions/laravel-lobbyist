<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Stringable;
use WiserWebSolutions\Lobbyist\Enums\Party;

/**
 * The computed political lean of a chamber (or any set of legislators), derived
 * from party affiliation counts.
 *
 * The label is based on the percentage spread between the two major parties'
 * share of the two-party total (Democrat + Republican; independents/others are
 * excluded from the spread calculation but included in {@see detail()}):
 *
 *   - spread < 10 points  => "Neutral"
 *   - spread 10-30 points => "Slight {Party}"
 *   - spread > 30 points  => "Strong {Party}"
 *
 * A two-party total of zero (no Democrats or Republicans at all) also resolves
 * to "Neutral", since the spread is otherwise undefined.
 */
final class Lean implements Stringable
{
    public function __construct(
        public readonly int $democrat,
        public readonly int $republican,
        public readonly int $independent,
        public readonly int $other,
    ) {}

    public static function fromLegislators(LegislatorCollection $legislators): self
    {
        return new self(
            democrat: $legislators->byParty(Party::Democrat)->count(),
            republican: $legislators->byParty(Party::Republican)->count(),
            independent: $legislators->byParty(Party::Independent)->count(),
            other: $legislators->byParty(Party::Other)->count(),
        );
    }

    public function label(): string
    {
        $twoPartyTotal = $this->democrat + $this->republican;

        if ($twoPartyTotal === 0 || $this->democrat === $this->republican) {
            return 'Neutral';
        }

        $spread = abs($this->democrat - $this->republican) / $twoPartyTotal * 100;

        if ($spread < 10) {
            return 'Neutral';
        }

        $party = $this->democrat > $this->republican ? Party::Democrat : Party::Republican;
        $strength = $spread > 30 ? 'Strong' : 'Slight';

        return "{$strength} {$party->label()}";
    }

    public function __toString(): string
    {
        return $this->label();
    }

    /**
     * The label plus a full party-count breakdown, e.g.
     * "Strong Democrat (120 Democrats, 30 Republicans, 2 Independents)".
     */
    public function detail(): string
    {
        $parts = [
            "{$this->democrat} Democrats",
            "{$this->republican} Republicans",
        ];

        if ($this->independent > 0) {
            $parts[] = "{$this->independent} Independents";
        }

        if ($this->other > 0) {
            $parts[] = "{$this->other} Other";
        }

        return $this->label().' ('.implode(', ', $parts).')';
    }
}
