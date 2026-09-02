<?php

namespace WiserWebSolutions\Lobbyist\Contracts\Providers;

use WiserWebSolutions\Lobbyist\Data\CommitteeAssignmentCollection;

/**
 * Publishes who sits on which committee, and in what role.
 *
 * A distinct capability from listing legislators because the two answers come
 * from different places and one is far rarer: plenty of sources will tell you
 * who the members of a chamber are, and very few will tell you who chairs its
 * education committee. A driver that cannot answer the second should not have
 * to pretend.
 */
interface CommitteeAssignmentProvider
{
    public function committeeAssignments(): CommitteeAssignmentCollection;
}
