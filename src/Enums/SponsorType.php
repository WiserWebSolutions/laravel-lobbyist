<?php

namespace WiserWebSolutions\Lobbyist\Enums;

/**
 * A legislator's sponsorship relationship to a bill.
 */
enum SponsorType: string
{
    case Primary = 'primary';
    case CoSponsor = 'co_sponsor';
    case JointSponsor = 'joint_sponsor';
    case Sponsor = 'sponsor';

    public function label(): string
    {
        return match ($this) {
            self::Primary => 'Primary Sponsor',
            self::CoSponsor => 'Co-Sponsor',
            self::JointSponsor => 'Joint Sponsor',
            self::Sponsor => 'Sponsor',
        };
    }

    /**
     * Resolve from a source value. LegiScan encodes sponsor types as integers
     * (0 = generic sponsor, 1 = primary, 2 = co-sponsor, 3 = joint) but the
     * textual forms are accepted too.
     */
    public static function fromString(int|string|null $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return match (strtolower(trim((string) $value))) {
            '1', 'primary', 'primary sponsor' => self::Primary,
            '2', 'co', 'co_sponsor', 'co-sponsor', 'cosponsor' => self::CoSponsor,
            '3', 'joint', 'joint_sponsor', 'joint sponsor' => self::JointSponsor,
            '0', 'sponsor' => self::Sponsor,
            default => null,
        };
    }
}
