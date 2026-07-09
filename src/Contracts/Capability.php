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
    case ListRepresentatives = 'list_representatives';
    case GetRepresentative = 'get_representative';
}
