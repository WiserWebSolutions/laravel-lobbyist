<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;

/**
 * A single version of a bill's literal text (e.g. as introduced, amended, or
 * enrolled), source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   id        int|string   version/document identifier
 *   bill_id   int|string|null
 *   type      string       e.g. "Introduced", "Amended", "Printer's Number 2"
 *   mime      string       e.g. "text/html", "application/pdf"
 *   date      string|CarbonInterface|null
 *   url       string       link to view/download this version
 *   content   string|null  the document's bytes/text, when the driver already
 *                           fetched them; null means fetch `url` yourself
 *
 * The raw driver payload may be preserved on `meta` so nothing is lost.
 */
final class BillText extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $id;

    #[Computed]
    public int|string|null $billId;

    #[Computed]
    public string $type;

    #[Computed]
    public string $mime;

    #[Computed]
    public ?CarbonInterface $date;

    #[Computed]
    public string $url;

    #[Computed]
    public ?string $content;

    public function __construct(public array $meta)
    {
        $this->id = $this->meta['id'] ?? 0;
        $this->billId = $this->meta['bill_id'] ?? null;
        $this->type = self::parseString($this->meta['type'] ?? '');
        $this->mime = self::parseString($this->meta['mime'] ?? '');
        $this->date = self::parseDate($this->meta['date'] ?? null);
        $this->url = self::parseString($this->meta['url'] ?? '');
        $this->content = $this->meta['content'] ?? null;
    }
}
