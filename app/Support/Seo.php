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
}
