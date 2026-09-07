<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\ChamberSessionDay;
use WiserWebSolutions\Lobbyist\Data\ChamberSessionDayCollection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class ChamberSessionDayTest extends TestCase
{
    public function test_it_derives_from_normalized_meta(): void
    {
        $day = new ChamberSessionDay(meta: [
            'chamber' => 'senate',
            'date' => '2026-09-28',
            'voting_day' => false,
            'identifier' => 'senate-2026-09-28',
            'url' => 'https://example.test/senate/session/info?SessDate=09/28/2026',
        ]);

        $this->assertSame(Chamber::Senate, $day->chamber);
        $this->assertSame('2026-09-28', $day->date?->format('Y-m-d'));
        $this->assertFalse($day->votingDay);
        $this->assertSame('senate-2026-09-28', $day->identifier);
    }

    public function test_voting_day_defaults_true_when_unspecified(): void
    {
        $day = new ChamberSessionDay(meta: ['chamber' => 'house', 'date' => '2026-01-01']);

        $this->assertTrue($day->votingDay);
    }

    public function test_is_null_safe_on_empty_meta(): void
    {
        $day = new ChamberSessionDay(meta: []);

        $this->assertNull($day->chamber);
        $this->assertNull($day->date);
        $this->assertSame('', $day->identifier);
        $this->assertFalse($day->isUpcoming());
    }

    public function test_is_upcoming_reflects_the_date(): void
    {
        $past = new ChamberSessionDay(meta: ['date' => now()->subWeek()->toDateString()]);
        $future = new ChamberSessionDay(meta: ['date' => now()->addWeek()->toDateString()]);

        $this->assertFalse($past->isUpcoming());
        $this->assertTrue($future->isUpcoming());
    }

    public function test_collection_filters_by_chamber(): void
    {
        $days = new ChamberSessionDayCollection([
            new ChamberSessionDay(meta: ['chamber' => 'house', 'date' => '2026-01-01']),
            new ChamberSessionDay(meta: ['chamber' => 'senate', 'date' => '2026-01-02']),
        ]);

        $this->assertCount(1, $days->byChamber(Chamber::Senate));
    }

    public function test_collection_upcoming_excludes_past_days_and_sorts_soonest_first(): void
    {
        $days = new ChamberSessionDayCollection([
            new ChamberSessionDay(meta: ['identifier' => 'past', 'date' => now()->subDay()->toDateString()]),
            new ChamberSessionDay(meta: ['identifier' => 'later', 'date' => now()->addWeeks(2)->toDateString()]),
            new ChamberSessionDay(meta: ['identifier' => 'sooner', 'date' => now()->addWeek()->toDateString()]),
        ]);

        $this->assertSame(['sooner', 'later'], $days->upcoming()->pluck('identifier')->all());
    }

    public function test_collection_voting_days_excludes_non_voting_days(): void
    {
        $days = new ChamberSessionDayCollection([
            new ChamberSessionDay(meta: ['identifier' => 'nv', 'voting_day' => false]),
            new ChamberSessionDay(meta: ['identifier' => 'voting', 'voting_day' => true]),
        ]);

        $this->assertSame(['voting'], $days->votingDays()->pluck('identifier')->all());
    }
}
