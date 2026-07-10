<?php

namespace WiserWebSolutions\Lobbyist\Tests\Data;

use WiserWebSolutions\Lobbyist\Data\Lean;
use WiserWebSolutions\Lobbyist\Tests\TestCase;

class LeanTest extends TestCase
{
    public function test_a_tie_is_neutral(): void
    {
        $lean = new Lean(democrat: 50, republican: 50, independent: 0, other: 0);

        $this->assertSame('Neutral', (string) $lean);
    }

    public function test_a_two_point_spread_is_neutral(): void
    {
        // 51/49 => a 2pt spread, well under the 10pt threshold.
        $lean = new Lean(democrat: 51, republican: 49, independent: 0, other: 0);

        $this->assertSame('Neutral', (string) $lean);
    }

    public function test_exactly_ten_points_is_slight(): void
    {
        // 55/45 => a 10pt spread => the boundary belongs to "Slight".
        $lean = new Lean(democrat: 55, republican: 45, independent: 0, other: 0);

        $this->assertSame('Slight Democrat', (string) $lean);
    }

    public function test_exactly_thirty_points_is_still_slight(): void
    {
        // 65/35 => a 30pt spread => the boundary still belongs to "Slight" (needs > 30 for Strong).
        $lean = new Lean(democrat: 35, republican: 65, independent: 0, other: 0);

        $this->assertSame('Slight Republican', (string) $lean);
    }

    public function test_over_thirty_points_is_strong(): void
    {
        // 66/34 => a 32pt spread.
        $lean = new Lean(democrat: 66, republican: 34, independent: 0, other: 0);

        $this->assertSame('Strong Democrat', (string) $lean);
    }

    public function test_zero_two_party_total_is_neutral(): void
    {
        $lean = new Lean(democrat: 0, republican: 0, independent: 4, other: 1);

        $this->assertSame('Neutral', (string) $lean);
    }

    public function test_detail_omits_independents_and_other_when_zero(): void
    {
        $lean = new Lean(democrat: 3, republican: 2, independent: 0, other: 0);

        $this->assertSame('Slight Democrat (3 Democrats, 2 Republicans)', $lean->detail());
    }

    public function test_detail_includes_independents_and_other_when_present(): void
    {
        $lean = new Lean(democrat: 120, republican: 30, independent: 2, other: 1);

        $this->assertSame(
            'Strong Democrat (120 Democrats, 30 Republicans, 2 Independents, 1 Other)',
            $lean->detail()
        );
    }
}
