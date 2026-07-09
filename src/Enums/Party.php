<?php

namespace WiserWebSolutions\Lobbyist\Enums;

/**
 * Political party affiliation.
 */
enum Party: string
{
    case Democrat = 'D';
    case Republican = 'R';
    case Independent = 'I';
    case Other = 'O';

    public function label(): string
    {
        return match ($this) {
            self::Democrat => 'Democrat',
            self::Republican => 'Republican',
            self::Independent => 'Independent',
            self::Other => 'Other',
        };
    }

    /**
     * Best-effort resolution from a source string.
     *
     * Accepts single-letter codes ("D"/"R"/"I") or full names.
     * Unknown / empty values resolve to {@see Party::Other}.
     */
    public static function fromString(?string $value): self
    {
        if ($value === null || trim($value) === '') {
            return self::Other;
        }

        return match (strtolower(trim($value))) {
            'd', 'democrat', 'democratic' => self::Democrat,
            'r', 'republican' => self::Republican,
            'i', 'independent' => self::Independent,
            default => self::Other,
        };
    }
}
