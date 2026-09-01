<?php

namespace WiserWebSolutions\Lobbyist\Tests\Enums;

use WiserWebSolutions\Lobbyist\Enums\SponsorType;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class SponsorTypeTest extends TestCase
{
    /**
     * LegiScan encodes sponsor types numerically, so these mappings are the
     * contract between its schema and ours.
     */
    public function test_resolves_from_numeric_source_values(): void
    {
        $this->assertSame(SponsorType::Sponsor, SponsorType::fromString(0));
        $this->assertSame(SponsorType::Primary, SponsorType::fromString(1));
        $this->assertSame(SponsorType::CoSponsor, SponsorType::fromString(2));
        $this->assertSame(SponsorType::JointSponsor, SponsorType::fromString(3));
    }

    public function test_resolves_from_textual_source_values(): void
    {
        $this->assertSame(SponsorType::Primary, SponsorType::fromString('Primary Sponsor'));
        $this->assertSame(SponsorType::CoSponsor, SponsorType::fromString('co-sponsor'));
        $this->assertSame(SponsorType::CoSponsor, SponsorType::fromString('cosponsor'));
        $this->assertSame(SponsorType::JointSponsor, SponsorType::fromString('  Joint  '));
    }

    public function test_returns_null_for_missing_or_unknown_values(): void
    {
        $this->assertNull(SponsorType::fromString(null));
        $this->assertNull(SponsorType::fromString(''));
        $this->assertNull(SponsorType::fromString('benefactor'));
    }

    public function test_every_case_has_a_label(): void
    {
        foreach (SponsorType::cases() as $case) {
            $this->assertNotSame('', $case->label());
        }
    }
}
