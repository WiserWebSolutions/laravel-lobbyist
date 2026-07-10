<?php

namespace WiserWebSolutions\Lobbyist\Contracts;

use WiserWebSolutions\Lobbyist\Data\ChamberCollection;
use WiserWebSolutions\Lobbyist\Data\Session;

/**
 * The base contract every legislative data driver must satisfy.
 *
 * Concrete data operations live on the segregated provider/lookup interfaces
 * (e.g. {@see Providers\BillProvider}, {@see Providers\BillLookup}). A driver
 * implements only the interfaces it can actually back with its data source and
 * advertises them through {@see capabilities()}.
 */
interface LobbyistDriver
{
    /**
     * Set the state context (two-letter abbreviation, e.g. "PA") the driver
     * should scope subsequent queries to.
     */
    public function setStateContext(string $state): static;

    /**
     * The currently active state context, if any.
     */
    public function stateContext(): ?string;

    /**
     * The legislative chambers this driver's state has (e.g. House + Senate),
     * each scoped to serve chamber-filtered data via the driver.
     */
    public function chambers(): ChamberCollection;

    /**
     * The most recent legislative session for the active state context.
     *
     * Throws {@see \WiserWebSolutions\Lobbyist\Exceptions\UnsupportedOperationException}
     * if the driver doesn't implement {@see Providers\SessionProvider}, or
     * {@see \WiserWebSolutions\Lobbyist\Exceptions\LobbyistException} if it does
     * but reports no sessions at all.
     */
    public function session(): Session;

    /**
     * The operations this driver supports.
     *
     * @return list<Capability>
     */
    public function capabilities(): array;

    /**
     * Whether the driver supports a given operation.
     */
    public function supports(Capability $capability): bool;
}
