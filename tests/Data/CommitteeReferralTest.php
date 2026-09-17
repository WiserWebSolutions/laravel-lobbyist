<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\CommitteeReferral;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class CommitteeReferralTest extends TestCase
{
    public function test_maps_the_recognized_fields(): void
    {
        $referral = new CommitteeReferral(meta: [
            'committee_id' => 'H:EDUCATION',
            'name' => 'Education',
            'chamber' => 'H',
            'date' => '2025-01-08',
        ]);

        $this->assertSame('H:EDUCATION', $referral->committeeId);
        $this->assertSame('Education', $referral->name);
        $this->assertSame('H', $referral->chamber);
        $this->assertSame('2025-01-08', $referral->date->toDateString());
    }

    public function test_defaults_when_the_source_omits_fields(): void
    {
        $referral = new CommitteeReferral(meta: []);

        $this->assertSame(0, $referral->committeeId);
        $this->assertSame('', $referral->name);
        $this->assertNull($referral->chamber);
        $this->assertNull($referral->date);
    }
}
