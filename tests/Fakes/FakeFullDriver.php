<?php

namespace WiserWebSolutions\Lobbyist\Tests\Fakes;

use WiserWebSolutions\Lobbyist\Contracts\Providers\BillChangeProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillTextHistoryLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillTextLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillTextVersionLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\CommitteeAssignmentProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\CommitteeScheduleProvider;
use WiserWebSolutions\Lobbyist\Contracts\DatasetArchive;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillVoteProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\DatasetLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\DatasetProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\LegislatorProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\SessionProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\SponsoredBillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\BillCollection;
use WiserWebSolutions\Lobbyist\Data\BillText;
use WiserWebSolutions\Lobbyist\Data\CommitteeAssignment;
use WiserWebSolutions\Lobbyist\Data\CommitteeAssignmentCollection;
use WiserWebSolutions\Lobbyist\Data\CommitteeMeeting;
use WiserWebSolutions\Lobbyist\Data\CommitteeMeetingCollection;
use WiserWebSolutions\Lobbyist\Data\BillTextCollection;
use WiserWebSolutions\Lobbyist\Data\Dataset;
use WiserWebSolutions\Lobbyist\Data\DatasetCollection;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\LegislatorCollection;
use WiserWebSolutions\Lobbyist\Data\Session;
use WiserWebSolutions\Lobbyist\Data\SessionCollection;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Data\VoteCast;
use WiserWebSolutions\Lobbyist\Data\VoteCollection;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Support\AbstractDriver;
use WiserWebSolutions\Lobbyist\Support\ZipDatasetArchive;

/**
 * A driver that supports every capability — stands in for a rich API driver
 * like LegiScan in core tests.
 */
class FakeFullDriver extends AbstractDriver implements
    SessionProvider,
    BillProvider,
    BillLookup,
    BillChangeProvider,
    BillVoteProvider,
    VoteProvider,
    VoteLookup,
    LegislatorProvider,
    RepresentativeLookup,
    SponsoredBillProvider,
    BillTextLookup,
    BillTextVersionLookup,
    CommitteeAssignmentProvider,
    CommitteeScheduleProvider,
    BillTextHistoryLookup,
    DatasetProvider,
    DatasetLookup
{
    public function datasets(): DatasetCollection
    {
        return new DatasetCollection([
            new Dataset(meta: [
                'session_id' => 2,
                'session_name' => 'Current Session',
                'hash' => 'dataset-hash-2',
                'date' => '2026-08-30',
                'size' => 1024,
                'access_key' => 'fake-access-key',
            ]),
        ]);
    }

    public function dataset(Dataset|int|string $session): DatasetArchive
    {
        $dataset = $session instanceof Dataset
            ? $session
            : $this->datasets()->forSession($session);

        return new ZipDatasetArchive(
            dataset: $dataset ?? $this->datasets()->first(),
            path: FixtureArchive::minimal(),
            mappers: [
                'bill' => fn (array $payload) => new Bill(meta: [
                    'id' => $payload['bill_id'] ?? 0,
                    'number' => $payload['bill_number'] ?? '',
                    'change_hash' => $payload['change_hash'] ?? null,
                ]),
                'vote' => fn (array $payload) => new Vote(meta: [
                    'id' => $payload['roll_call_id'] ?? 0,
                    'bill_id' => $payload['bill_id'] ?? null,
                    'positions' => array_map(
                        fn (array $cast) => new VoteCast(meta: [
                            'legislator_id' => $cast['people_id'] ?? 0,
                            'position' => $cast['vote_id'] ?? null,
                        ]),
                        $payload['votes'] ?? []
                    ),
                ]),
                'people' => fn (array $payload) => new Legislator(meta: [
                    'id' => $payload['people_id'] ?? 0,
                    'name' => $payload['name'] ?? '',
                ]),
            ],
        );
    }

    public function sessions(): SessionCollection
    {
        return new SessionCollection([
            new Session(meta: ['id' => 1, 'name' => 'Old Session', 'prior' => true]),
            new Session(meta: ['id' => 2, 'name' => 'Current Session', 'prior' => false, 'sine_die' => false]),
        ]);
    }

    public function bills(): BillCollection
    {
        return new BillCollection([
            new Bill(meta: ['id' => 1, 'number' => 'HB1', 'chamber' => 'house']),
            new Bill(meta: ['id' => 2, 'number' => 'SB1', 'chamber' => 'senate']),
        ]);
    }

    public function bill(string|int $identifier): Bill
    {
        return new Bill(meta: [
            'id' => $identifier,
            'change_hash' => 'hash-'.$identifier,
            'votes' => [
                new Vote(meta: ['id' => 91, 'bill_id' => $identifier, 'chamber' => 'house', 'yea' => 2, 'nay' => 1]),
            ],
            'sponsors' => [
                new Legislator(meta: ['id' => 4, 'name' => 'House Rep 4', 'sponsor_type' => 'primary']),
            ],
        ]);
    }

    public function billChanges(): BillCollection
    {
        return new BillCollection([
            new Bill(meta: ['id' => 1, 'number' => 'HB1', 'change_hash' => 'aaa']),
            new Bill(meta: ['id' => 2, 'number' => 'SB1', 'change_hash' => 'bbb']),
        ]);
    }

    public function votesForBill(string|int $identifier): VoteCollection
    {
        return $this->bill($identifier)->votes();
    }

    public function sponsoredBills(string|int $personId): BillCollection
    {
        return new BillCollection([
            new Bill(meta: ['id' => 7, 'number' => 'HB7', 'sponsors' => [
                new Legislator(meta: ['id' => $personId, 'sponsor_type' => 'primary']),
            ]]),
        ]);
    }

    public function votes(): VoteCollection
    {
        return new VoteCollection([
            new Vote(meta: ['id' => 1, 'chamber' => 'house']),
            new Vote(meta: ['id' => 2, 'chamber' => 'senate']),
        ]);
    }

    public function vote(string|int $identifier): Vote
    {
        return new Vote(meta: ['id' => $identifier]);
    }

    public function legislators(): LegislatorCollection
    {
        return new LegislatorCollection([
            // House: 3 Democrats, 2 Republicans => 20pt spread => "Slight Democrat".
            new Legislator(meta: ['id' => 1, 'name' => 'House Rep 1', 'chamber' => 'house', 'party' => 'D']),
            new Legislator(meta: ['id' => 2, 'name' => 'House Rep 2', 'chamber' => 'house', 'party' => 'D']),
            new Legislator(meta: ['id' => 3, 'name' => 'House Rep 3', 'chamber' => 'house', 'party' => 'D']),
            new Legislator(meta: ['id' => 4, 'name' => 'House Rep 4', 'chamber' => 'house', 'party' => 'R']),
            new Legislator(meta: ['id' => 5, 'name' => 'House Rep 5', 'chamber' => 'house', 'party' => 'R']),
            // Senate: 1 Democrat, 4 Republicans, 1 Independent => 60pt spread => "Strong Republican".
            new Legislator(meta: ['id' => 6, 'name' => 'Senate Rep 1', 'chamber' => 'senate', 'party' => 'D']),
            new Legislator(meta: ['id' => 7, 'name' => 'Senate Rep 2', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 8, 'name' => 'Senate Rep 3', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 9, 'name' => 'Senate Rep 4', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 10, 'name' => 'Senate Rep 5', 'chamber' => 'senate', 'party' => 'R']),
            new Legislator(meta: ['id' => 11, 'name' => 'Senate Rep 6', 'chamber' => 'senate', 'party' => 'I']),
        ]);
    }

    public function representatives(): LegislatorCollection
    {
        return $this->legislators()->byChamber(Chamber::House);
    }

    public function senators(): LegislatorCollection
    {
        return $this->legislators()->byChamber(Chamber::Senate);
    }

    public function representative(string|int $identifier): Legislator
    {
        return new Legislator(meta: ['id' => $identifier]);
    }

    public function billText(string|int $identifier): BillText
    {
        return $this->billTextHistory($identifier)->latest();
    }

    public function committeeAssignments(): CommitteeAssignmentCollection
    {
        return new CommitteeAssignmentCollection([
            new CommitteeAssignment(meta: [
                'committee' => 'Education',
                'chamber' => 'house',
                'legislator_id' => 1,
                'legislator_name' => 'Ada Alpha',
                'district' => '1',
                'party' => 'D',
                'position' => 'Chair',
            ]),
            new CommitteeAssignment(meta: [
                'committee' => 'Education',
                'chamber' => 'house',
                'legislator_id' => 2,
                'legislator_name' => 'Bob Beta',
                'district' => '2',
                'party' => 'R',
                'position' => 'Vice Chair',
            ]),
        ]);
    }

    public function committeeMeetings(): CommitteeMeetingCollection
    {
        return new CommitteeMeetingCollection([
            new CommitteeMeeting(meta: [
                'committee' => 'Education',
                'chamber' => 'house',
                'date' => now()->addWeek()->toDateString(),
                'time' => '9:30 AM',
                'location' => 'Room 140 Main Capitol',
                'identifier' => 'fake-hearing-1',
            ]),
        ]);
    }

    public function billTextVersion(string|int $textIdentifier): BillText
    {
        return new BillText(meta: [
            'id' => $textIdentifier,
            'bill_id' => 1,
            'type' => 'Amended',
            'mime' => 'application/pdf',
            'content' => 'version '.$textIdentifier,
        ]);
    }

    public function billTextHistory(string|int $identifier): BillTextCollection
    {
        return new BillTextCollection([
            new BillText(meta: ['id' => 1, 'bill_id' => $identifier, 'type' => 'Introduced', 'date' => '2024-01-01']),
            new BillText(meta: ['id' => 2, 'bill_id' => $identifier, 'type' => 'Amended', 'date' => '2024-02-01']),
        ]);
    }
}
