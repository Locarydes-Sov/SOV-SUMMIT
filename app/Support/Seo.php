<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Builds public SEO links (canonical, og:image, sitemap) on one domain.
 */
class Seo
{
    public const SITE_NAME = 'SOV SUMMIT';

    public const DEFAULT_OG_IMAGE = 'assets/img/og/sov-summit-og.jpg';

    public static function baseUrl(): string
    {
        return rtrim((string) config('app.canonical_url'), '/');
    }

    /**
     * Put any URL or path on the canonical domain, without a trailing slash and without a query string.
     */
    public static function canonical(?string $url = null): string
    {
        $path = (string) parse_url($url ?? request()->getPathInfo(), PHP_URL_PATH);
        $path = '/'.trim($path, '/');

        return $path === '/' ? self::baseUrl().'/' : self::baseUrl().$path;
    }

    /**
     * Make an image URL absolute. Local paths are put on the canonical domain; external URLs are kept.
     */
    public static function imageUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return self::baseUrl().'/'.self::DEFAULT_OG_IMAGE;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            $host = (string) parse_url($url, PHP_URL_HOST);

            return in_array($host, [request()->getHost(), parse_url(self::baseUrl(), PHP_URL_HOST)], true)
                ? self::baseUrl().'/'.ltrim((string) parse_url($url, PHP_URL_PATH), '/')
                : $url;
        }

        return self::baseUrl().'/'.ltrim($url, '/');
    }

    /**
     * Add " | SOV SUMMIT" once, even when the stored title already ends with it.
     */
    public static function title(string $title): string
    {
        $title = trim(preg_replace('/(\s*\|\s*'.preg_quote(self::SITE_NAME, '/').')+\s*$/i', '', $title) ?? $title);

        return $title.' | '.self::SITE_NAME;
    }

    /**
     * Stable id of the organisation, so other schema blocks can point to it.
     */
    public static function organizationId(): string
    {
        return self::baseUrl().'/#organization';
    }

    /**
     * Filled brand profile URLs (LinkedIn, Crunchbase, …) for the schema "sameAs" list.
     *
     * @return array<int, string>
     */
    public static function sameAs(): array
    {
        return array_values(array_filter((array) config('seo.profiles', []), fn ($url): bool => is_string($url) && $url !== ''));
    }

    /**
     * Footer social links that have a URL set.
     *
     * @return array<string, string>
     */
    public static function socialLinks(): array
    {
        $profiles = (array) config('seo.profiles', []);

        return collect((array) config('seo.footer_social', []))
            ->mapWithKeys(fn (string $network): array => [$network => (string) ($profiles[$network] ?? '')])
            ->filter()
            ->all();
    }

    /**
     * Full Organization schema, shared by every page.
     *
     * @return array<string, mixed>
     */
    public static function organization(): array
    {
        $org = (array) config('seo.organization', []);
        $address = (array) ($org['address'] ?? []);

        return array_filter([
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => $org['name'] ?? self::SITE_NAME,
            'legalName' => $org['legal_name'] ?? null,
            'url' => self::baseUrl().'/',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => self::baseUrl().'/'.ltrim((string) ($org['logo'] ?? ''), '/'),
            ],
            'email' => $org['email'] ?? null,
            'telephone' => $org['telephone'] ?? null,
            'foundingDate' => $org['founding_date'] ?? null,
            'slogan' => $org['slogan'] ?? null,
            'areaServed' => $org['area_served'] ?? null,
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $address['street'] ?? null,
                'addressLocality' => $address['locality'] ?? null,
                'postalCode' => $address['postal_code'] ?? null,
                'addressCountry' => $address['country'] ?? null,
            ],
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'contactType' => 'customer service',
                'email' => $org['email'] ?? null,
                'telephone' => $org['telephone'] ?? null,
                'areaServed' => $org['area_served'] ?? null,
                'availableLanguage' => ['English'],
            ],
            'sameAs' => self::sameAs() ?: null,
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    /**
     * Short reference to the organisation for use inside other schema (organizer, provider, publisher).
     *
     * @return array<string, mixed>
     */
    public static function organizationReference(): array
    {
        $org = (array) config('seo.organization', []);

        return [
            '@type' => 'Organization',
            '@id' => self::organizationId(),
            'name' => $org['name'] ?? self::SITE_NAME,
            'url' => self::baseUrl().'/',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => self::baseUrl().'/'.ltrim((string) ($org['logo'] ?? ''), '/'),
            ],
        ];
    }

    /**
     * Print a schema array as a JSON-LD script tag body.
     *
     * @param  array<string, mixed>  $schema
     */
    public static function jsonLd(array $schema): string
    {
        return (string) json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }

    /**
     * Meta description of at most $limit characters, cut at a word boundary.
     */
    public static function description(string $text, int $limit = 160): string
    {
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text);

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit - 1);
        $lastSpace = mb_strrpos($cut, ' ');

        return rtrim(mb_substr($cut, 0, $lastSpace ?: mb_strlen($cut)), " ,;:.-").'…';
    }
}
