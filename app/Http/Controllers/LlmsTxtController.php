<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Event;
use App\Models\Service;
use App\Support\Seo;
use Illuminate\Http\Response;

/**
 * Serves /llms.txt: a short, plain-text map of the site for AI assistants and answer engines.
 *
 * @see https://llmstxt.org
 */
class LlmsTxtController extends Controller
{
    public function __invoke(): Response
    {
        $org = (array) config('seo.organization');
        $address = (array) ($org['address'] ?? []);
        $base = Seo::baseUrl();

        $lines = [
            '# '.($org['name'] ?? Seo::SITE_NAME),
            '',
            '> '.($org['name'] ?? Seo::SITE_NAME).' is the operating brand of '.($org['legal_name'] ?? '').', based in '
                .($address['locality'] ?? '').', Switzerland. It plans and coordinates international events, conferences, '
                .'government and executive delegations, management training, travel experiences, security coordination '
                .'and media coverage, with one team as the single point of contact.',
            '',
            '- Email: '.($org['email'] ?? ''),
            '- Phone: '.($org['telephone'] ?? ''),
            '- Address: '.trim(($address['street'] ?? '').', '.($address['postal_code'] ?? '').' '.($address['locality'] ?? '').', Switzerland', ', '),
            '- Contact form: '.$base.'/contact',
            '',
            '## Services',
            '',
        ];

        foreach (Service::query()->published()->ordered()->get() as $service) {
            $lines[] = '- ['.$service->title.']('.Seo::canonical($service->publicUrl()).')'.$this->note($service->seo_description ?: $service->summary);
        }

        $upcoming = Event::query()->published()->where('event_date', '>=', now()->startOfDay())->orderBy('event_date')->get();

        if ($upcoming->isNotEmpty()) {
            $lines = [...$lines, '', '## Upcoming programmes', ''];

            foreach ($upcoming as $event) {
                $lines[] = '- ['.$event->title.']('.$base.'/events/'.$event->slug.')'.$this->note($event->summary);
            }
        }

        $posts = Blog::query()->published()->orderByDesc('published_at')->limit(20)->get();

        if ($posts->isNotEmpty()) {
            $lines = [...$lines, '', '## Insights', ''];

            foreach ($posts as $post) {
                $lines[] = '- ['.$post->title.']('.$base.'/blog/'.$post->slug.')'.$this->note($post->summary);
            }
        }

        $lines = [...$lines, '', '## Key pages', '',
            '- [About]('.$base.'/about)',
            '- [All services]('.$base.'/services)',
            '- [Events and case studies]('.$base.'/events)',
            '- [Special offers: chauffeur tours and travel packages]('.$base.'/special-offers)',
            '- [FAQ]('.$base.'/faq): services, vendor selection, pricing, VIP and diplomatic delegations',
            '- [Contact]('.$base.'/contact)',
            '',
            '## Optional',
            '',
            '- [Legal notice]('.$base.'/legal-notice)',
            '- [Privacy policy]('.$base.'/privacy-policy)',
            '- [Terms and conditions]('.$base.'/terms-conditions)',
            '- [Sitemap]('.$base.'/sitemap.xml)',
        ];

        return response(implode("\n", $lines)."\n", 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }

    private function note(?string $text): string
    {
        $text = trim(strip_tags((string) $text));

        return $text === '' ? '' : ': '.Seo::description($text, 160);
    }
}
