<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Davos 2027" event (WEF Annual Meeting week) with SEO fields
 * and AI-friendly content sections: key facts, services, logistics,
 * gallery, FAQ (+ FAQPage JSON-LD) and a call to action.
 *
 * Dates and venue: davos.ch — World Economic Forum Annual Meeting 2027,
 * 18–22 January 2027, Kongresszentrum Davos.
 *
 * Photos: Unsplash (free Unsplash License), all tagged Davos, Switzerland.
 */
return new class extends Migration
{
    private const SLUG = 'davos-2027-wef-annual-meeting-executive-delegation-programme';

    private const EVENT_TYPE = 'App\\Models\\Event';

    public function up(): void
    {
        if (DB::table('events')->where('slug', self::SLUG)->exists()) {
            return;
        }

        $now = now();
        $images = $this->images();

        DB::transaction(function () use ($now, $images): void {
            $eventId = DB::table('events')->insertGetId([
                'title' => 'Davos 2027: Executive Delegation Programme during the WEF Annual Meeting',
                'slug' => self::SLUG,
                'seo_title' => 'Davos 2027 WEF Week Delegation Logistics | SOV SUMMIT',
                'summary' => 'SOV SUMMIT coordinates executive delegations in Davos during the World Economic Forum Annual Meeting, 18–22 January 2027: accommodation, chauffeured transport, meeting venues, security coordination and on-site support — planned and run by one team from Zug, Switzerland.',
                'seo_keywords' => 'Davos 2027, WEF 2027, World Economic Forum Annual Meeting 2027, Davos delegation management, Davos logistics, Davos accommodation WEF week, Davos chauffeur service, Zurich Airport to Davos transfer, Davos side event venue, executive travel Switzerland, SOV SUMMIT',
                'description' => $this->description(),
                'cover_image' => $images['aerial']['src'],
                'event_date' => '2027-01-18 08:00:00',
                'location' => 'Davos, Switzerland',
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $sections = $this->sections($images);

            foreach ($sections as $position => [$type, $short, $data]) {
                DB::table('content_sections')->insert([
                    'sectionable_type' => self::EVENT_TYPE,
                    'sectionable_id' => $eventId,
                    'page_type' => 'event',
                    'type' => $type,
                    'short' => $short,
                    'position' => $position + 1,
                    'data' => json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'is_published' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        $eventId = DB::table('events')->where('slug', self::SLUG)->value('id');

        if (! $eventId) {
            return;
        }

        DB::transaction(function () use ($eventId): void {
            DB::table('content_sections')
                ->where('sectionable_type', self::EVENT_TYPE)
                ->where('sectionable_id', $eventId)
                ->delete();

            DB::table('events')->where('id', $eventId)->delete();
        });
    }

    /**
     * @return array<string, array{src: string, alt: string, credit: string}>
     */
    private function images(): array
    {
        $url = fn (string $id, int $width = 1600): string => "https://images.unsplash.com/{$id}?auto=format&fit=crop&w={$width}&q=80";

        return [
            'aerial' => [
                'src' => $url('photo-1587952134350-d10487bc1c37', 1920),
                'alt' => 'Aerial view of Davos town in its alpine valley, Switzerland',
                'credit' => 'Prateek Keshari / Unsplash',
            ],
            'peaks' => [
                'src' => $url('photo-1581196659434-70bf4293b04f'),
                'alt' => 'Snow-covered peaks above Davos under a clear blue sky',
                'credit' => 'Damian Markutt / Unsplash',
            ],
            'sertig' => [
                'src' => $url('photo-1543871529-0de50cad443d'),
                'alt' => 'Wooden barn in Sertig Dörfli near Davos in deep winter snow',
                'credit' => 'Damian Markutt / Unsplash',
            ],
            'fluela' => [
                'src' => $url('photo-1481829148746-3f52c9d57d3c'),
                'alt' => 'Flüela Wisshorn mountain near Davos in winter',
                'credit' => 'Jürg Gutknecht / Unsplash',
            ],
            'ridge' => [
                'src' => $url('photo-1622036255522-9b8c28de7873'),
                'alt' => 'Snowy alpine ridge in the Davos region on a sunny winter day',
                'credit' => 'Sven / Unsplash',
            ],
            'clouds' => [
                'src' => $url('photo-1450873622936-c13d06e998e5'),
                'alt' => 'Snow-covered mountain under low winter clouds near Davos',
                'credit' => 'Dino Reichmuth / Unsplash',
            ],
        ];
    }

    /**
     * Answer-first body copy: the first paragraph and the fact list answer
     * who / what / when / where, which search engines and AI assistants quote.
     */
    private function description(): string
    {
        return <<<'HTML'
<p><strong>SOV SUMMIT runs a fully coordinated executive delegation programme in Davos, Switzerland, during the World Economic Forum Annual Meeting from 18 to 22 January 2027.</strong> We plan and manage accommodation, chauffeured ground transport, private meeting and side-event venues, security coordination and on-site support, so that leaders can focus on their meetings while one team handles everything else.</p>

<h2>Davos 2027 at a glance</h2>
<ul>
  <li><strong>What:</strong> Delegation management and logistics during the WEF Annual Meeting week</li>
  <li><strong>When:</strong> 18–22 January 2027 (we recommend arriving on 17 January)</li>
  <li><strong>Where:</strong> Davos, Canton of Graubünden, Switzerland — official meeting venue: Davos Congress Centre</li>
  <li><strong>Who it is for:</strong> CEOs, boards, government and institutional delegations, family offices, investors and their teams</li>
  <li><strong>Coordinated by:</strong> SOV SUMMIT (Sovereign Summit GmbH), Zug, Switzerland</li>
</ul>

<h2>Why Davos week needs early planning</h2>
<p>During the Annual Meeting, a small mountain town hosts thousands of leaders, staff, media and security teams at the same time. Hotel rooms and apartments are limited and are often reserved many months in advance. Roads, parking and access are tightly controlled, and schedules change by the hour. Delegations that arrive with confirmed accommodation, transport and meeting space gain time, privacy and calm — and avoid last-minute costs.</p>

<h2>What SOV SUMMIT coordinates</h2>
<ul>
  <li><strong>Accommodation:</strong> hotel rooms, chalets and apartments in Davos and Klosters, matched to delegation size and security needs</li>
  <li><strong>Ground transport:</strong> chauffeured vehicles, Zurich Airport transfers and on-call cars during the week</li>
  <li><strong>Meeting and side-event venues:</strong> private rooms for bilateral meetings, breakfasts, dinners and receptions</li>
  <li><strong>Delegation management:</strong> agendas, meeting scheduling, guest lists and a single point of contact</li>
  <li><strong>Security coordination:</strong> liaison with protection teams, secure routing and discreet arrivals</li>
  <li><strong>Media coverage:</strong> photography, video and interview support for your Davos presence</li>
</ul>

<h2>How the programme works</h2>
<ol>
  <li><strong>Briefing:</strong> we confirm goals, dates, delegation size and budget.</li>
  <li><strong>Plan:</strong> you receive one clear proposal for accommodation, transport, venues and staffing.</li>
  <li><strong>Secure:</strong> we book and confirm all suppliers and share a live run-of-show.</li>
  <li><strong>Deliver:</strong> our team is on the ground in Davos for the full week.</li>
  <li><strong>Review:</strong> after the meeting, you receive a short debrief and cost summary.</li>
</ol>

<h2>Getting to Davos</h2>
<p>Most international delegations fly into Zurich Airport (ZRH). Davos is about 150 km away — roughly two to two and a half hours by road in normal winter conditions, longer during peak arrival days. Rail connections run via Landquart. Helicopter transfers are possible, subject to weather and permits. SOV SUMMIT plans each arrival and departure around your meeting schedule.</p>

<p><em>SOV SUMMIT is an independent coordination company and is not affiliated with the World Economic Forum. Participation in the official Annual Meeting is by invitation of the World Economic Forum. Our services cover logistics, hospitality and private programmes around the meeting.</em></p>
HTML;
    }

    /**
     * @param  array<string, array{src: string, alt: string, credit: string}>  $images
     * @return array<int, array{0: string, 1: string, 2: array<string, mixed>}>
     */
    private function sections(array $images): array
    {
        $faq = $this->faq();

        return [
            ['stats', 'Davos 2027 key facts', [
                'eyebrow' => 'Key facts',
                'heading' => 'Davos 2027 in numbers.',
                'lede' => 'The essentials for planning a delegation during the World Economic Forum Annual Meeting.',
                'items' => [
                    ['number' => '18–22 Jan', 'label' => 'WEF Annual Meeting 2027'],
                    ['number' => '~3,000', 'label' => 'Leaders expected in Davos'],
                    ['number' => '1,560 m', 'label' => 'Altitude of Davos'],
                    ['number' => '~150 km', 'label' => 'From Zurich Airport'],
                ],
            ]],

            ['card_grid', 'Davos services', [
                'eyebrow' => 'What we coordinate',
                'heading' => 'One team for your entire Davos week.',
                'lede' => 'Every element of your delegation, planned together and managed on the ground.',
                'columns' => 3,
                'cards' => [
                    ['title' => 'Accommodation', 'description' => 'Hotels, chalets and apartments in Davos and Klosters, secured early and matched to your team.', 'image' => $images['sertig']['src']],
                    ['title' => 'Chauffeured transport', 'description' => 'Zurich Airport transfers and on-call cars with experienced winter drivers.', 'image' => $images['ridge']['src']],
                    ['title' => 'Meeting venues', 'description' => 'Private rooms for bilaterals, breakfasts, dinners and side events.', 'image' => $images['peaks']['src']],
                    ['title' => 'Delegation management', 'description' => 'Agendas, scheduling, guest lists and one point of contact all week.', 'image' => $images['aerial']['src']],
                    ['title' => 'Security coordination', 'description' => 'Liaison with protection teams, secure routing and discreet arrivals.', 'image' => $images['clouds']['src']],
                    ['title' => 'Media coverage', 'description' => 'Photo, video and interview support for your presence in Davos.', 'image' => $images['fluela']['src']],
                ],
            ]],

            ['image_text', 'Arriving in Davos', [
                'eyebrow' => 'Logistics',
                'heading' => 'Arrive calm. Move without delay.',
                'lede' => 'Davos is a small alpine town. During the Annual Meeting, every minute on the road counts.',
                'body' => '<p>We plan arrivals from Zurich Airport around your first meeting, not the other way round. Vehicles, drivers and routes are confirmed in advance, with backup options for snow and traffic controls.</p><ul><li>Meet-and-greet at Zurich Airport</li><li>Winter-equipped premium vehicles</li><li>On-call cars throughout the week</li><li>Real-time coordination by our on-site team</li></ul>',
                'image' => $images['aerial']['src'],
                'image_alt' => $images['aerial']['alt'],
                'align' => 'right',
            ]],

            ['gallery', 'Davos gallery', [
                'eyebrow' => 'Davos, Switzerland',
                'heading' => 'The setting.',
                'lede' => 'High in the Swiss Alps, Davos combines a global stage with a quiet mountain backdrop.',
                'columns' => 3,
                'images' => array_map(fn (array $image): array => [
                    'src' => $image['src'],
                    'alt' => $image['alt'],
                    'caption' => $image['alt'].' — Photo: '.$image['credit'],
                ], array_values(array_intersect_key($images, array_flip(['peaks', 'sertig', 'fluela', 'ridge', 'clouds', 'aerial'])))),
            ]],

            ['accordion', 'Davos 2027 FAQ', [
                'eyebrow' => 'FAQ',
                'heading' => 'Davos 2027: common questions.',
                'lede' => 'Short answers for delegations planning their week in Davos.',
                'items' => array_map(fn (array $item): array => ['q' => $item['q'], 'a' => '<p>'.$item['a'].'</p>'], $faq),
            ]],

            ['raw_html', 'FAQ structured data (JSON-LD)', [
                'html' => '<script type="application/ld+json">'.json_encode([
                    '@context' => 'https://schema.org',
                    '@type' => 'FAQPage',
                    'mainEntity' => array_map(fn (array $item): array => [
                        '@type' => 'Question',
                        'name' => $item['q'],
                        'acceptedAnswer' => ['@type' => 'Answer', 'text' => $item['a']],
                    ], $faq),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG).'</script>',
            ]],

            ['cta_band', 'Davos 2027 call to action', [
                'eyebrow' => 'Davos 2027',
                'heading' => 'Plan your Davos week now.',
                'subheading' => 'Accommodation in Davos is limited. Share your delegation size and dates, and we will send a clear proposal.',
                'cta_label' => 'Discuss your Davos programme',
                'cta_href' => '/contact',
                'variant' => 'dark',
            ]],
        ];
    }

    /**
     * Plain-text answers, reused for the visible FAQ and the FAQPage schema.
     *
     * @return array<int, array{q: string, a: string}>
     */
    private function faq(): array
    {
        return [
            [
                'q' => 'When is the World Economic Forum Annual Meeting 2027 in Davos?',
                'a' => 'The World Economic Forum Annual Meeting 2027 takes place from 18 to 22 January 2027 in Davos, Switzerland. The main venue is the Davos Congress Centre.',
            ],
            [
                'q' => 'Do I need an invitation to attend the WEF Annual Meeting?',
                'a' => 'Yes. Participation in the official Annual Meeting is by invitation of the World Economic Forum. SOV SUMMIT does not sell access to WEF sessions. We coordinate logistics, hospitality and private meetings for delegations around the meeting.',
            ],
            [
                'q' => 'What does SOV SUMMIT coordinate in Davos?',
                'a' => 'SOV SUMMIT coordinates accommodation, chauffeured transport, Zurich Airport transfers, private meeting and side-event venues, delegation scheduling, security coordination and media coverage, with one team on the ground for the full week.',
            ],
            [
                'q' => 'How far is Davos from Zurich Airport?',
                'a' => 'Davos is about 150 km from Zurich Airport. The drive takes roughly two to two and a half hours in normal winter conditions and can take longer on peak arrival days. Rail travel runs via Landquart, and helicopter transfers are possible subject to weather and permits.',
            ],
            [
                'q' => 'When should a delegation start planning for Davos 2027?',
                'a' => 'As early as possible. Accommodation in Davos is very limited during the Annual Meeting and is often reserved many months ahead. We recommend starting planning at least three to four months before the meeting.',
            ],
            [
                'q' => 'Can SOV SUMMIT organise private dinners and side events in Davos?',
                'a' => 'Yes. We source private rooms and venues for bilateral meetings, breakfasts, dinners and receptions, and manage guest lists, catering and on-site coordination.',
            ],
            [
                'q' => 'How do we start planning with SOV SUMMIT?',
                'a' => 'Contact SOV SUMMIT with your delegation size, travel dates and main goals for the week. We reply with a clear proposal covering accommodation, transport, venues and staffing.',
            ],
        ];
    }
};
