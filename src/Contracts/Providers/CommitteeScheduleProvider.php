<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\CommitteeMeetingCollection;

/**
 * Publishes when committees next meet in public.
 *
 * Separate from bill data because it is forward-looking: everything else a
 * driver returns describes what has already happened, and a hearing that has
 * not happened yet is the one piece of legislative information somebody can
 * still act on.
 */
interface CommitteeScheduleProvider
{
    public function committeeMeetings(): CommitteeMeetingCollection;
}
