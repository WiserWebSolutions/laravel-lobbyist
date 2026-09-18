<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;

/**
 * A normalized legislative bill, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below; core
 * does not know about any specific data source. Recognized keys (all optional):
 *
 *   id                 int|string
 *   number             string   e.g. "HB1234"
 *   title              string
 *   description        string
 *   state              StateEnum
 *   chamber            Chamber|null  (falls back to inference from `number`)
 *   status             string
 *   status_date        string|CarbonInterface|null
 *   last_action        string
 *   last_action_date   string|CarbonInterface|null
 *   url                string
 *   memo               string|null   sponsor's cosponsorship memo subject; see {@see $memo}
 *   memo_url           string|null   where that memo is published, when the source links it
 *   session_id         int|null
 *   change_hash        string|null   opaque source revision marker; see {@see $changeHash}
 *   texts              BillTextCollection|array<BillText>  see {@see texts()}
 *   votes              VoteCollection|array<Vote>            see {@see votes()}
 *   sponsors           LegislatorCollection|array<Legislator> see {@see sponsors()}
 *   history            BillHistoryEntryCollection|array<BillHistoryEntry> see {@see history()}
 *   referrals          CommitteeReferralCollection|array<CommitteeReferral> see {@see referrals()}
 *
 * The raw driver payload may be preserved on `meta` so nothing is lost.
 */
final class Bill extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $id;

    #[Computed]
    public string $number;

    #[Computed]
    public string $title;

    #[Computed]
    public string $description;

    #[Computed]
    public StateEnum $state;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public string $status;

    #[Computed]
    public ?CarbonInterface $statusDate;

    #[Computed]
    public string $lastAction;

    #[Computed]
    public ?CarbonInterface $lastActionDate;

    #[Computed]
    public string $url;

    #[Computed]
    public ?int $sessionId;

    /**
     * The subject line of the sponsor's cosponsorship memo -- the circulated
     * "please join me on this bill" notice -- when the source publishes one.
     *
     * It reads as a plain-language title and is often the only human summary
     * a bill has before its text is drafted, so it is worth surfacing next to
     * {@see $title} rather than folding into {@see $description}. Null on
     * sources with no such concept (most of them); {@see $memoUrl} points at
     * the memo itself where the source links it.
     */
    #[Computed]
    public ?string $memo;

    #[Computed]
    public ?string $memoUrl;

    /**
     * An opaque marker for the source revision of this bill, when the driver
     * exposes one (LegiScan calls it `change_hash`). Comparing it against a
     * stored copy tells a consumer whether re-fetching the full bill would
     * yield anything new, which is the cheapest way to stay inside an API
     * quota: no field-by-field comparison and no wasted detail requests.
     */
    #[Computed]
    public ?string $changeHash;

    public function __construct(public array $meta)
    {
        $this->id = $this->meta['id'] ?? 0;
        $this->number = self::parseString($this->meta['number'] ?? '');
        $this->title = self::parseString($this->meta['title'] ?? '');
        $this->description = self::parseString($this->meta['description'] ?? '');
        $this->state = ($this->meta['state'] ?? null) instanceof StateEnum
            ? $this->meta['state']
            : StateEnum::US;
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromBillNumber($this->number);
        $this->status = self::parseString($this->meta['status'] ?? '');
        $this->statusDate = self::parseDate($this->meta['status_date'] ?? null);
        $this->lastAction = self::parseString($this->meta['last_action'] ?? '');
        $this->lastActionDate = self::parseDate($this->meta['last_action_date'] ?? null);
        $this->url = self::parseString($this->meta['url'] ?? '');
        $this->sessionId = self::parseIntOrNull($this->meta['session_id'] ?? null);
        $this->memo = self::nonEmptyStringOrNull($this->meta['memo'] ?? null);
        $this->memoUrl = self::nonEmptyStringOrNull($this->meta['memo_url'] ?? null);
        $this->changeHash = $this->meta['change_hash'] ?? null;
    }

    /**
     * Every version of this bill's text (introduced, amended, enrolled, ...),
     * oldest first. Drivers map these into `meta['texts']`; absent that, this
     * is empty.
     */
    public function texts(): BillTextCollection
    {
        $texts = $this->meta['texts'] ?? [];

        return $texts instanceof BillTextCollection ? $texts : new BillTextCollection($texts);
    }

    /**
     * The most recent version of this bill's text — never null. When no
     * version is mapped at all, this returns an empty {@see BillText} whose
     * `toHTML()`/`toPDF()`/`toString()` throw on use rather than the caller
     * needing a null check here.
     */
    public function text(): BillText
    {
        return $this->texts()->latest() ?? new BillText(meta: [
            'bill_id' => $this->number !== '' ? $this->number : $this->id,
        ]);
    }

    /**
     * Every roll call taken on this bill, when the driver embeds them.
     *
     * Sources commonly return roll call summaries inside the bill payload, so
     * this is usually free — no request beyond the one that produced the bill.
     * Per-member detail lives on {@see Vote::positions()}, which may require a
     * separate lookup.
     */
    public function votes(): VoteCollection
    {
        $votes = $this->meta['votes'] ?? [];

        return $votes instanceof VoteCollection ? $votes : new VoteCollection($votes);
    }

    /**
     * The legislators sponsoring this bill, primary sponsor(s) first where the
     * source orders them. Each entry carries its `sponsor_type` and
     * `sponsor_order` on `meta`, since sponsorship describes the relationship
     * to this bill rather than the member.
     */
    public function sponsors(): LegislatorCollection
    {
        $sponsors = $this->meta['sponsors'] ?? [];

        return $sponsors instanceof LegislatorCollection
            ? $sponsors
            : new LegislatorCollection($sponsors);
    }

    /**
     * The bill's procedural actions, in the order the source reports them.
     *
     * Every driver is expected to populate this the same way -- a source
     * whose only account of a bill's actions is a free-text list (rather than
     * a status code) maps it here, so nothing downstream needs to reach past
     * this DTO into a driver-specific raw payload to reconstruct a timeline.
     */
    public function history(): BillHistoryEntryCollection
    {
        $history = $this->meta['history'] ?? [];

        return $history instanceof BillHistoryEntryCollection
            ? $history
            : new BillHistoryEntryCollection($history);
    }

    /**
     * The committees this bill has been referred to, in the order the source
     * reports them.
     */
    public function referrals(): CommitteeReferralCollection
    {
        $referrals = $this->meta['referrals'] ?? [];

        return $referrals instanceof CommitteeReferralCollection
            ? $referrals
            : new CommitteeReferralCollection($referrals);
    }
}
