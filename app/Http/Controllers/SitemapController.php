<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Event;
use App\Models\Page;
use App\Models\Service;
use App\Services\Localrydes\SpecialOfferApiException;
use App\Services\Localrydes\SpecialOfferClient;
use App\Support\Seo;
use App\Support\SpecialOfferUrl;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SitemapController extends Controller
{
    /**
     * Public pages that always exist, with their relative priority.
     *
     * @var array<string, string>
     */
    private const STATIC_PAGES = [
        '/' => '1.0',
        '/about' => '0.8',
        '/services' => '0.9',
        '/products' => '0.7',
        '/events' => '0.9',
        '/special-offers' => '0.7',
        '/blog' => '0.7',
        '/insights' => '0.6',
        '/contact' => '0.8',
        '/faq' => '0.6',
        '/legal-notice' => '0.2',
        '/privacy-policy' => '0.2',
        '/cookie-policy' => '0.2',
        '/terms-conditions' => '0.2',
    ];

    /** How many special offers to ask the LocalRydes API for in one request. */
    private const OFFERS_PER_REQUEST = 100;

    /** Safety limit for special-offer pages, so a large catalogue cannot slow the sitemap down. */
    private const MAX_OFFER_REQUESTS = 5;

    public function __construct(private SpecialOfferClient $offers) {}

    public function __invoke(): Response
    {
        $listingLastModified = $this->listingLastModified();

        $urls = collect(self::STATIC_PAGES)
            ->map(fn (string $priority, string $path): array => [
                'loc' => Seo::canonical($path),
                'lastmod' => $listingLastModified[$path] ?? null,
                'priority' => $priority,
            ])
            ->values();

        $urls = $urls
            ->concat($this->entries(Event::query()->published()->get(['slug', 'updated_at']), '/events/', '0.8'))
            ->concat($this->serviceEntries())
            ->concat($this->entries(Blog::query()->published()->get(['slug', 'updated_at']), '/blog/', '0.6'))
            ->concat($this->entries(Page::query()->published()->get(['slug', 'updated_at']), '/', '0.5'))
            ->concat($this->specialOfferEntries())
            ->unique('loc')
            ->values();

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * @param  iterable<int, object{slug: string, updated_at: ?Carbon}>  $records
     * @return array<int, array{loc: string, lastmod: ?string, priority: string}>
     */
    private function entries(iterable $records, string $prefix, string $priority): array
    {
        $entries = [];

        foreach ($records as $record) {
            $entries[] = [
                'loc' => Seo::canonical($prefix.$record->slug),
                'lastmod' => $record->updated_at?->toAtomString(),
                'priority' => $priority,
            ];
        }

        return $entries;
    }

    /**
     * Last change date for listing pages, taken from the newest published record they show.
     *
     * @return array<string, string>
     */
    private function listingLastModified(): array
    {
        $events = Event::query()->published()->max('updated_at');
        $services = Service::query()->published()->max('updated_at');
        $blogs = Blog::query()->published()->max('updated_at');

        $dates = array_filter([
            '/events' => $events,
            '/services' => $services,
            '/blog' => $blogs,
            '/' => collect([$events, $services, $blogs])->filter()->max(),
        ]);

        return array_map(fn ($date): string => Carbon::parse($date)->toAtomString(), $dates);
    }

    /**
     * Services use their public URL, so Management Training is listed once as /management-training.
     *
     * @return array<int, array{loc: string, lastmod: ?string, priority: string}>
     */
    private function serviceEntries(): array
    {
        return Service::query()->published()->get(['slug', 'updated_at'])
            ->map(fn (Service $service): array => [
                'loc' => Seo::canonical($service->publicUrl()),
                'lastmod' => $service->updated_at?->toAtomString(),
                'priority' => '0.8',
            ])
            ->all();
    }

    /**
     * Special offers come from the LocalRydes API. If the API is down, the sitemap is still served without them.
     *
     * @return array<int, array{loc: string, lastmod: ?string, priority: string}>
     */
    private function specialOfferEntries(): array
    {
        $entries = [];

        try {
            for ($request = 0; $request < self::MAX_OFFER_REQUESTS; $request++) {
                $offers = $this->offers->list([
                    'limit' => self::OFFERS_PER_REQUEST,
                    'skip' => $request * self::OFFERS_PER_REQUEST,
                ]);

                foreach ($offers as $offer) {
                    if ((string) data_get($offer, 'uuid') === '') {
                        continue;
                    }

                    $entries[] = [
                        'loc' => Seo::canonical(SpecialOfferUrl::for($offer)),
                        'lastmod' => null,
                        'priority' => '0.6',
                    ];
                }

                if (count($offers) < self::OFFERS_PER_REQUEST) {
                    break;
                }
            }
        } catch (SpecialOfferApiException) {
            return $entries;
        }

        return $entries;
    }
}
