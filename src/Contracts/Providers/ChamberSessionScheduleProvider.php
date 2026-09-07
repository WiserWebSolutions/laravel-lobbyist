<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\ChamberSessionDayCollection;

/**
 * Publishes the days a chamber itself is (or was) in session.
 *
 * Distinct from {@see CommitteeScheduleProvider}: that answers "when does a
 * committee meet"; this answers "when does the chamber convene" — neither a
 * bill list nor a committee schedule can answer that, and a bill's own
 * "next floor calendar" listing (where a driver publishes one) still
 * doesn't say which day that is, only that one is coming.
 */
interface ChamberSessionScheduleProvider
{
    public function chamberSessionDays(): ChamberSessionDayCollection;
}
