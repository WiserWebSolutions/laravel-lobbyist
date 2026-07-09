<?php

namespace WiserWebSolutions\Lobbyist\Enums;

/**
 * An individual legislator's position on a roll call.
 */
enum VotePosition: string
{
    case Yea = 'yea';
    case Nay = 'nay';
    case NotVoting = 'not_voting';
    case Absent = 'absent';

    public function label(): string
    {
        return match ($this) {
            self::Yea => 'Yea',
            self::Nay => 'Nay',
            self::NotVoting => 'Not Voting',
            self::Absent => 'Absent',
        };
    }

    /**
     * Resolve from a source value. LegiScan encodes positions as integers
     * (1 = Yea, 2 = Nay, 3 = Not Voting, 4 = Absent) but also exposes text.
     */
    public static function fromString(int|string|null $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return match (strtolower(trim((string) $value))) {
            '1', 'yea', 'yes', 'aye' => self::Yea,
            '2', 'nay', 'no' => self::Nay,
            '3', 'not_voting', 'nv', 'not voting' => self::NotVoting,
            '4', 'absent' => self::Absent,
            default => null,
        };
    }
}
