<?php

namespace WiserWebSolutions\Lobbyist\Support;

use Illuminate\Support\Str;
use WiserWebSolutions\Lobbyist\Contracts\Capability;
use WiserWebSolutions\Lobbyist\Contracts\LobbyistDriver;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\BillProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\RepresentativeProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\SessionProvider;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteLookup;
use WiserWebSolutions\Lobbyist\Contracts\Providers\VoteProvider;
use WiserWebSolutions\Lobbyist\Data\Bill;
use WiserWebSolutions\Lobbyist\Data\Legislator;
use WiserWebSolutions\Lobbyist\Data\Vote;
use WiserWebSolutions\Lobbyist\Exceptions\UnsupportedOperationException;

/**
 * Convenience base for drivers.
 *
 * Implements the shared state-context plumbing and derives {@see capabilities()}
 * from the provider/lookup interfaces the concrete driver actually implements,
 * so capability advertising can never drift from the type system. It also
 * supplies runtime safety-net implementations of the lookup methods that throw
 * {@see UnsupportedOperationException} unless the driver overrides them.
 */
abstract class AbstractDriver implements LobbyistDriver
{
    protected ?string $stateContext = null;

    /**
     * Maps each capability to the interface a driver must implement to claim it.
     *
     * @var array<string, class-string>
     */
    private const CAPABILITY_INTERFACES = [
        Capability::ListSessions->value => SessionProvider::class,
        Capability::ListBills->value => BillProvider::class,
        Capability::GetBill->value => BillLookup::class,
        Capability::ListVotes->value => VoteProvider::class,
        Capability::GetVote->value => VoteLookup::class,
        Capability::ListRepresentatives->value => RepresentativeProvider::class,
        Capability::GetRepresentative->value => RepresentativeLookup::class,
    ];

    public function setStateContext(string $state): static
    {
        $this->stateContext = (string) Str::of($state)->upper()->trim();

        return $this;
    }

    public function stateContext(): ?string
    {
        return $this->stateContext;
    }

    /**
     * @return list<Capability>
     */
    public function capabilities(): array
    {
        $capabilities = [];

        foreach (self::CAPABILITY_INTERFACES as $value => $interface) {
            if ($this instanceof $interface) {
                $capabilities[] = Capability::from($value);
            }
        }

        return $capabilities;
    }

    public function supports(Capability $capability): bool
    {
        $interface = self::CAPABILITY_INTERFACES[$capability->value] ?? null;

        return $interface !== null && $this instanceof $interface;
    }

    public function bill(string|int $identifier): Bill
    {
        throw UnsupportedOperationException::for($this, 'bill');
    }

    public function vote(string|int $identifier): Vote
    {
        throw UnsupportedOperationException::for($this, 'vote');
    }

    public function representative(string|int $identifier): Legislator
    {
        throw UnsupportedOperationException::for($this, 'representative');
    }
}
