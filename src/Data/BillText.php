<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Carbon\CarbonInterface;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Exceptions\LobbyistException;

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
 *   url       string       link to view/download this version, in `mime`
 *   links     array<string,string>  additional format => url pairs beyond
 *                           `mime`/`url`, when a source offers more than one
 *                           rendering of the same version (e.g. an HTML page
 *                           plus a PDF)
 *   content   string|null  the document's bytes/text, when the driver already
 *                           fetched them; null means fetch `url` yourself
 *
 * The raw driver payload may be preserved on `meta` so nothing is lost.
 *
 * {@see toHTML()}, {@see toPDF()}, and {@see toString()} offer this version in
 * whichever formats the driver's source actually makes available — each
 * throws {@see LobbyistException} rather than returning a bogus value when
 * this version doesn't have that format (e.g. `toPDF()` on an entry whose only
 * link is an HTML page). {@see Bill::text()} guarantees a usable instance even
 * when a driver has mapped no versions at all, so calling code is always safe
 * to chain `->toHTML()` etc. without a null check — the throw happens there
 * instead.
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

    /** @var array<string,string> */
    #[Computed]
    public array $links;

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
        $this->links = $this->meta['links'] ?? [];
        $this->content = $this->meta['content'] ?? null;
    }

    /**
     * A link to view/download the HTML rendering of this version.
     */
    public function toHTML(): string
    {
        return $this->linkFor('text/html', 'toHTML');
    }

    /**
     * A link to view/download a PDF rendering of this version.
     */
    public function toPDF(): string
    {
        return $this->linkFor('application/pdf', 'toPDF');
    }

    /**
     * The literal text of this version.
     */
    public function toString(): string
    {
        if ($this->content === null) {
            throw $this->unsupported('toString');
        }

        return $this->content;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function linkFor(string $expectedMime, string $method): string
    {
        if ($this->mime === $expectedMime && $this->url !== '') {
            return $this->url;
        }

        if (($this->links[$expectedMime] ?? '') !== '') {
            return $this->links[$expectedMime];
        }

        throw $this->unsupported($method);
    }

    private function unsupported(string $method): LobbyistException
    {
        return LobbyistException::driverError(
            "Bill [{$this->billId}] does not have bill text support ({$method}())."
        );
    }
}
