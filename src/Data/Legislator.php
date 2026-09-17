<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;
use WiserWebSolutions\Lobbyist\Data\Concerns\ParsesValues;
use WiserWebSolutions\Lobbyist\Enums\Chamber;
use WiserWebSolutions\Lobbyist\Enums\Party;
use WiserWebSolutions\Lobbyist\Enums\StateEnum;

/**
 * A normalized elected representative / legislator, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   id           int|string
 *   name         string
 *   first_name   string
 *   last_name    string
 *   party        Party|string|null
 *   chamber      Chamber|string|null
 *   district     string|null
 *   role         string|null
 *   state        StateEnum
 *   active       bool|null
 *   url               string
 *   image_url         string|null   see {@see $imageUrl} and {@see image()}
 *   county            string|null
 *   capitol_phone     string|null
 *   district_phone    string|null
 *   capitol_address   LegislatorAddress|array|null  see {@see $capitolAddress}
 *   district_address  LegislatorAddress|array|null  see {@see $districtAddress}
 *
 * No email, website, or social-account contract exists here: neither
 * installed driver (LegiScan, palegis) publishes any of those, so declaring
 * one would just repeat the mistake of a contract nothing populates.
 */
final class Legislator extends Data
{
    use ParsesValues;

    #[Computed]
    public int|string $id;

    #[Computed]
    public string $name;

    #[Computed]
    public string $firstName;

    #[Computed]
    public string $lastName;

    #[Computed]
    public Party $party;

    #[Computed]
    public ?Chamber $chamber;

    #[Computed]
    public ?string $district;

    #[Computed]
    public ?string $role;

    #[Computed]
    public StateEnum $state;

    #[Computed]
    public ?bool $active;

    #[Computed]
    public string $url;

    /**
     * An official portrait, where the source publishes one.
     *
     * Worth a first-class property rather than a dig through `meta`: a
     * directory of legislators without faces is noticeably harder to use, and
     * whether a source provides them varies enough that callers need to ask.
     */
    #[Computed]
    public ?string $imageUrl;

    /**
     * The county or counties the district covers.
     *
     * Districts are numbered, which tells a constituent nothing. The county is
     * how people actually locate themselves.
     */
    #[Computed]
    public ?string $county;

    #[Computed]
    public ?string $capitolPhone;

    #[Computed]
    public ?string $districtPhone;

    #[Computed]
    public ?LegislatorAddress $capitolAddress;

    #[Computed]
    public ?LegislatorAddress $districtAddress;

    public function __construct(public array $meta)
    {
        $this->id = $this->meta['id'] ?? 0;
        $this->name = self::parseString($this->meta['name'] ?? '');
        $this->firstName = self::parseString($this->meta['first_name'] ?? '');
        $this->lastName = self::parseString($this->meta['last_name'] ?? '');
        $this->party = ($this->meta['party'] ?? null) instanceof Party
            ? $this->meta['party']
            : Party::fromString($this->meta['party'] ?? null);
        $this->chamber = ($this->meta['chamber'] ?? null) instanceof Chamber
            ? $this->meta['chamber']
            : Chamber::fromString($this->meta['chamber'] ?? null);
        $this->district = isset($this->meta['district']) ? (string) $this->meta['district'] : null;
        $this->role = isset($this->meta['role']) ? (string) $this->meta['role'] : null;
        $this->state = ($this->meta['state'] ?? null) instanceof StateEnum
            ? $this->meta['state']
            : StateEnum::US;
        $this->active = isset($this->meta['active']) ? (bool) $this->meta['active'] : null;
        $this->url = self::parseString($this->meta['url'] ?? '');
        $this->imageUrl = self::optionalString($this->meta['image_url'] ?? null);
        $this->county = self::optionalString($this->meta['county'] ?? null);
        $this->capitolPhone = self::optionalString($this->meta['capitol_phone'] ?? null);
        $this->districtPhone = self::optionalString($this->meta['district_phone'] ?? null);
        $this->capitolAddress = self::addressFrom($this->meta['capitol_address'] ?? null);
        $this->districtAddress = self::addressFrom($this->meta['district_address'] ?? null);
    }

    private static function addressFrom(mixed $value): ?LegislatorAddress
    {
        if ($value instanceof LegislatorAddress) {
            return $value;
        }

        return is_array($value) && $value !== [] ? new LegislatorAddress($value) : null;
    }

    /**
     * The official portrait as a local file, downloading and caching it to
     * the configured disk (`lobbyist.images`) on first access.
     *
     * Returns null when this legislator has no {@see $imageUrl}, or when the
     * download fails -- a missing photo is normal enough (not every source
     * publishes one, and a legislator may not have one yet) that a caller
     * shouldn't have to wrap every call in a try/catch.
     */
    public function image(): ?File
    {
        if ($this->imageUrl === null) {
            return null;
        }

        $disk = Storage::disk(Config::get('lobbyist.images.disk', 'local'));
        $path = $this->imagePath();

        if (! $disk->exists($path)) {
            try {
                $response = Http::timeout(15)->get($this->imageUrl);
            } catch (ConnectionException) {
                return null;
            }

            if ($response->failed()) {
                return null;
            }

            $disk->put($path, $response->body());
        }

        $fullPath = $disk->path($path);

        return is_file($fullPath) ? new File($fullPath) : null;
    }

    private function imagePath(): string
    {
        $directory = trim((string) Config::get('lobbyist.images.path', 'lobbyist/legislators'), '/');
        $extension = pathinfo(parse_url($this->imageUrl ?? '', PHP_URL_PATH) ?: '', PATHINFO_EXTENSION) ?: 'jpg';

        return "{$directory}/{$this->id}.{$extension}";
    }

    /**
     * A trimmed string, or null when the source published an empty element.
     *
     * RSS feeds emit `<parss:County/>` rather than omitting the field, so an
     * empty string arrives where a null is meant.
     */
    private static function optionalString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }
}
