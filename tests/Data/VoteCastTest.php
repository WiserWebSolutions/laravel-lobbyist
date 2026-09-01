<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Data\VoteCast;
use WiserWebSolutions\Lobbyist\Data\VoteCastCollection;
use WiserWebSolutions\Lobbyist\Enums\VotePosition;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class VoteCastTest extends TestCase
{
    public function test_resolves_a_position_from_a_numeric_source_value(): void
    {
        $cast = new VoteCast(meta: ['legislator_id' => 101, 'position' => 1]);

        $this->assertSame(VotePosition::Yea, $cast->position);
        $this->assertSame(101, $cast->legislatorId);
    }

    public function test_resolves_a_position_from_a_textual_source_value(): void
    {
        $this->assertSame(
            VotePosition::Nay,
            (new VoteCast(meta: ['position' => 'Nay']))->position
        );

        $this->assertSame(
            VotePosition::NotVoting,
            (new VoteCast(meta: ['position' => 'NV']))->position
        );
    }

    public function test_position_is_null_when_unrecognized(): void
    {
        $this->assertNull((new VoteCast(meta: ['position' => 'abstained']))->position);
        $this->assertNull((new VoteCast(meta: []))->position);
    }

    public function test_accepts_an_already_resolved_position(): void
    {
        $cast = new VoteCast(meta: ['position' => VotePosition::Absent]);

        $this->assertSame(VotePosition::Absent, $cast->position);
    }

    public function test_a_vote_exposes_no_positions_when_the_source_omits_them(): void
    {
        // Roll call summaries embedded in a bill carry tallies but no breakdown.
        $vote = new Vote(meta: ['id' => 1, 'yea' => 30, 'nay' => 12]);

        $this->assertInstanceOf(VoteCastCollection::class, $vote->positions());
        $this->assertTrue($vote->positions()->isEmpty());
        $this->assertSame(30, $vote->yea);
    }

    public function test_a_vote_exposes_individual_positions_when_present(): void
    {
        $vote = new Vote(meta: ['id' => 1, 'positions' => [
            new VoteCast(meta: ['legislator_id' => 1, 'position' => VotePosition::Yea]),
            new VoteCast(meta: ['legislator_id' => 2, 'position' => VotePosition::Yea]),
            new VoteCast(meta: ['legislator_id' => 3, 'position' => VotePosition::Nay]),
            new VoteCast(meta: ['legislator_id' => 4, 'position' => VotePosition::Absent]),
        ]]);

        $this->assertCount(4, $vote->positions());
        $this->assertCount(2, $vote->positions()->yeas());
        $this->assertCount(1, $vote->positions()->nays());
    }

    public function test_can_find_how_one_legislator_voted(): void
    {
        $vote = new Vote(meta: ['positions' => [
            new VoteCast(meta: ['legislator_id' => 1, 'position' => VotePosition::Yea]),
            new VoteCast(meta: ['legislator_id' => 2, 'position' => VotePosition::Nay]),
        ]]);

        $this->assertSame(VotePosition::Nay, $vote->positions()->forLegislator(2)?->position);

        // Source ids arrive as both ints and strings, so matching is loose.
        $this->assertSame(VotePosition::Yea, $vote->positions()->forLegislator('1')?->position);
        $this->assertNull($vote->positions()->forLegislator(999));
    }
}
