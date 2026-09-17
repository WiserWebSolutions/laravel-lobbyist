<?php

namespace WiserWebSolutions\Lobbyist\Tests\Enums;

use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;
use WiserWebSolutions\Lobbyist\Enums\VotePosition;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class EnumTest extends TestCase
{
    public function test_state_enum_basics(): void
    {
        $this->assertSame(38, StateEnum::PA->id());
        $this->assertSame('PA', StateEnum::PA->abbr());
        $this->assertSame('Pennsylvania', StateEnum::PA->label());
        $this->assertTrue(StateEnum::PA->isState());
        $this->assertFalse(StateEnum::US->isState());
        $this->assertSame(StateEnum::PA, StateEnum::fromAbbr('pa'));
        $this->assertNull(StateEnum::fromAbbr('ZZ'));
    }

    public function test_chamber_resolution(): void
    {
        $this->assertSame(Chamber::House, Chamber::fromString('H'));
        $this->assertSame(Chamber::Senate, Chamber::fromString('senate'));
        $this->assertSame(Chamber::House, Chamber::fromString('Rep'));
        $this->assertSame(Chamber::House, Chamber::fromBillNumber('HB1234'));
        $this->assertSame(Chamber::Senate, Chamber::fromBillNumber('SR12'));
        $this->assertNull(Chamber::fromBillNumber(''));
    }

    public function test_party_resolution(): void
    {
        $this->assertSame(Party::Democrat, Party::fromString('D'));
        $this->assertSame(Party::Republican, Party::fromString('republican'));
        $this->assertSame(Party::Other, Party::fromString(null));
        $this->assertSame(Party::Other, Party::fromString('Green'));
    }

    public function test_vote_position_resolution(): void
    {
        $this->assertSame(VotePosition::Yea, VotePosition::fromString('1'));
        $this->assertSame(VotePosition::Yea, VotePosition::fromString('Yea'));
        $this->assertSame(VotePosition::Nay, VotePosition::fromString('no'));
        // "No Vote"/"Leave" are how palegis.us labels these on its roll-call
        // pages -- aliases alongside LegiScan's own "not voting"/"absent".
        $this->assertSame(VotePosition::NotVoting, VotePosition::fromString('No Vote'));
        $this->assertSame(VotePosition::Absent, VotePosition::fromString('Leave'));
        $this->assertNull(VotePosition::fromString(null));
        $this->assertNull(VotePosition::fromString('unknown'));
    }
}
