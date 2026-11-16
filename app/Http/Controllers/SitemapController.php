<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Event;
use App\Models\Page;
use App\Models\Service;
use App\Support\Seo;
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
        '/management-training' => '0.8',
        '/products' => '0.7',
        '/events' => '0.9',
        '/special-offers' => '0.7',
        '/blog' => '0.7',
        '/insights' => '0.6',
        '/contact' => '0.8',
        '/legal-notice' => '0.2',
        '/privacy-policy' => '0.2',
        '/cookie-policy' => '0.2',
        '/terms-conditions' => '0.2',
    ];

    public function __invoke(): Response
    {
        $urls = collect(self::STATIC_PAGES)
            ->map(fn (string $priority, string $path): array => ['loc' => Seo::canonical($path), 'lastmod' => null, 'priority' => $priority])
            ->values();

        $urls = $urls
            ->concat($this->entries(Event::query()->published()->get(['slug', 'updated_at']), '/events/', '0.8'))
            ->concat($this->entries(Service::query()->published()->get(['slug', 'updated_at']), '/services/', '0.8'))
            ->concat($this->entries(Blog::query()->published()->get(['slug', 'updated_at']), '/blog/', '0.6'))
            ->concat($this->entries(Page::query()->published()->get(['slug', 'updated_at']), '/', '0.5'))
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
}
