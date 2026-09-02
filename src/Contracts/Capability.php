<?php

namespace WiserWebSolutions\Lobbyist\Contracts;

/**
 * Discrete operations a driver may support.
 *
 * Drivers advertise the subset they can fulfil via
 * {@see LobbyistDriver::capabilities()}. Consumers can branch on
 * {@see LobbyistDriver::supports()} instead of type-checking each interface.
 */
enum Capability: string
{
    case ListSessions = 'list_sessions';
    case ListBills = 'list_bills';
    case GetBill = 'get_bill';
    case ListVotes = 'list_votes';
    case GetVote = 'get_vote';
    case ListLegislators = 'list_legislators';
    case GetRepresentative = 'get_representative';
    case GetBillText = 'get_bill_text';
    case ListBillTextHistory = 'list_bill_text_history';
    case GetBillTextVersion = 'get_bill_text_version';
    case ListCommitteeAssignments = 'list_committee_assignments';
    case ListCommitteeMeetings = 'list_committee_meetings';
    case ListBillVotes = 'list_bill_votes';
    case ListBillChanges = 'list_bill_changes';
    case ListSponsoredBills = 'list_sponsored_bills';
    case ListDatasets = 'list_datasets';
    case GetDataset = 'get_dataset';
}
