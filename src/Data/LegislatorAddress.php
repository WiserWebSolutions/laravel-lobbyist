<?php

namespace WiserWebSolutions\Lobbyist\Data;

use Spatie\LaravelData\Attributes\Computed;
use Spatie\LaravelData\Data;

/**
 * A legislator's office address, source-agnostic.
 *
 * Drivers map their raw payloads into the normalized `meta` shape below.
 * Recognized keys (all optional):
 *
 *   street1         string|null
 *   street2         string|null
 *   city_state_zip  string|null
 */
final class LegislatorAddress extends Data
{
    #[Computed]
    public ?string $street1;

    #[Computed]
    public ?string $street2;

    #[Computed]
    public ?string $cityStateZip;

    public function __construct(public array $meta)
    {
        $this->street1 = self::optionalString($this->meta['street1'] ?? null);
        $this->street2 = self::optionalString($this->meta['street2'] ?? null);
        $this->cityStateZip = self::optionalString($this->meta['city_state_zip'] ?? null);
    }

    /**
     * A trimmed string, or null when the source published an empty element.
     *
     * RSS feeds emit `<parss:Street2/>` rather than omitting the field, so an
     * empty string arrives where a null is meant -- same reasoning as
     * {@see Legislator}'s own private helper of the same shape.
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
