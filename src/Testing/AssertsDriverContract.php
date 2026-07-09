<?php

namespace WiserWebSolutions\Lobbyist\Testing;

use PHPUnit\Framework\Assert;
use WiserWebSolutions\Lobbyist\Contracts\Capability;
use WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\SessionProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Exceptions\UnsupportedOperationException;

/**
 * Reusable assertions that a concrete driver honours the Lobbyist contract.
 *
 * Driver packages should run this against their driver (with any network calls
 * faked) to guarantee that advertised capabilities stay in sync with the
 * provider/lookup interfaces the driver implements.
 */
trait AssertsDriverContract
{
    /** @var array<string, class-string> */
    private array $capabilityInterfaces = [
        Capability::ListSessions->value => SessionProvider::class,
        Capability::ListBills->value => BillProvider::class,
        Capability::GetBill->value => BillLookup::class,
        Capability::ListVotes->value => VoteProvider::class,
        Capability::GetVote->value => VoteLookup::class,
        Capability::ListRepresentatives->value => RepresentativeProvider::class,
        Capability::GetRepresentative->value => RepresentativeLookup::class,
    ];

    protected function assertDriverContract(LobbyistDriver $driver): void
    {
        $advertised = $driver->capabilities();

        Assert::assertContainsOnlyInstancesOf(
            Capability::class,
            $advertised,
            'capabilities() must return only Capability instances.'
        );

        foreach ($this->capabilityInterfaces as $value => $interface) {
            $capability = Capability::from($value);
            $implementsInterface = $driver instanceof $interface;
            $claims = in_array($capability, $advertised, true);

            Assert::assertSame(
                $implementsInterface,
                $claims,
                "Capability [{$value}] advertised via capabilities() must match implementing [{$interface}]."
            );

            Assert::assertSame(
                $implementsInterface,
                $driver->supports($capability),
                "supports({$value}) must match implementing [{$interface}]."
            );
        }
    }

    /**
     * Assert that a lookup the driver does NOT support throws cleanly rather
     * than fataling (requires the driver to extend AbstractDriver or otherwise
     * provide the throwing method).
     */
    protected function assertUnsupportedLookupThrows(LobbyistDriver $driver, string $method, string|int $identifier = 1): void
    {
        try {
            $driver->{$method}($identifier);
            Assert::fail("Expected [{$method}] to throw UnsupportedOperationException.");
        } catch (UnsupportedOperationException) {
            Assert::assertTrue(true);
        }
    }
}
