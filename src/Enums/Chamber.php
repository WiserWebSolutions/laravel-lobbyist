<?php

namespace WiserWebSolutions\Lobbyist\Enums;

/**
 * Legislative chamber (body) an item belongs to.
 */
enum Chamber: string
{
    case House = 'house';
    case Senate = 'senate';
    case Joint = 'joint';

    public function label(): string
    {
        return match ($this) {
            self::House => 'House',
            self::Senate => 'Senate',
            self::Joint => 'Joint',
        };
    }

    /**
     * Best-effort resolution from arbitrary source strings.
     *
     * Accepts LegiScan chamber codes ("H"/"S"), full names, or feed labels.
     */
    public static function fromString(?string $value): ?self
    {
        if ($value === null) {
            return null;
        }

        return match (strtolower(trim($value))) {
            'h', 'house', 'lower', 'rep', 'representative', 'assembly' => self::House,
            's', 'senate', 'upper', 'sen', 'senator' => self::Senate,
            'j', 'joint' => self::Joint,
            default => null,
        };
    }

    /**
     * Infer the originating chamber from a bill number's prefix
     * (e.g. "HB1" / "HR5" => House, "SB2" / "SR3" => Senate).
     */
    public static function fromBillNumber(?string $billNumber): ?self
    {
        $billNumber = ltrim((string) $billNumber);

        if ($billNumber === '') {
            return null;
        }

        return match (strtoupper($billNumber[0])) {
            'H', 'A' => self::House, // A: some states use "A" (Assembly) for the lower chamber
            'S' => self::Senate,
            default => null,
        };
    }
}
