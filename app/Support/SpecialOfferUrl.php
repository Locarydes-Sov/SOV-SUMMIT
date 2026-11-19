<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds readable special-offer URLs: /special-offers/{uuid}/{title-slug}.
 *
 * The uuid is still needed to load the offer from the LocalRydes API; the slug adds keywords for search engines.
 */
class SpecialOfferUrl
{
    /**
     * @param  array<string, mixed>  $offer
     */
    public static function slug(array $offer): string
    {
        return Str::slug((string) data_get($offer, 'title'));
    }

    /**
     * @param  array<string, mixed>  $offer
     */
    public static function for(array $offer): string
    {
        return self::forUuid((string) data_get($offer, 'uuid'), (string) data_get($offer, 'title'));
    }

    public static function forUuid(string $uuid, string $title): string
    {
        $slug = Str::slug($title);

        return route('special-offers.show', $slug === '' ? ['uuid' => $uuid] : ['uuid' => $uuid, 'slug' => $slug]);
    }
}
