<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Doha Forum 2026" delegation programme with SEO fields and
 * AI-friendly content sections: key facts, services, logistics, gallery,
 * FAQ (+ FAQPage JSON-LD) and a call to action.
 *
 * Dates and venue: dohaforum.org — Doha Forum 2026, 5–6 December 2026,
 * Sheraton Grand Doha; partner-led events across Doha on 7 December.
 * Theme "Redefining Global Trust". Invitation-only event.
 *
 * Photos: Unsplash (free Unsplash License), all tagged Doha, Qatar.
 */
return new class extends Migration
{
    private const SLUG = 'doha-forum-2026-delegation-programme';

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
                'title' => 'Doha Forum 2026: Delegation Programme in Doha, Qatar',
                'slug' => self::SLUG,
                'seo_title' => 'Doha Forum 2026 Delegation Logistics | SOV SUMMIT',
                'summary' => 'SOV SUMMIT coordinates government, institutional and executive delegations in Doha during Doha Forum 2026, 5–6 December 2026: hotels, airport meet-and-greet, chauffeured transport, private meeting rooms and on-site support — planned and run by one team.',
                'seo_keywords' => 'Doha Forum 2026, Doha Forum delegation, Redefining Global Trust, Doha delegation management, Doha logistics December 2026, Sheraton Grand Doha, Hamad International Airport meet and greet, Doha chauffeur service, Qatar executive travel, private meeting rooms Doha, SOV SUMMIT',
                'description' => $this->description(),
                'cover_image' => $images['corniche']['src'],
                'event_date' => '2026-12-05 09:00:00',
                'location' => 'Doha, Qatar',
                'is_published' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($this->sections($images) as $position => [$type, $short, $data]) {
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
            'corniche' => [
                'src' => $url('photo-1596986343464-332d54fa5702', 1920),
                'alt' => 'Doha skyline seen across the water from Al Corniche, Qatar',
                'credit' => 'tarek suman / Unsplash',
            ],
            'westbay' => [
                'src' => $url('photo-1685113872064-de4180a0ea93'),
                'alt' => 'Towers of the Doha skyline in West Bay, Qatar',
                'credit' => 'Kenny / Unsplash',
            ],
            'night' => [
                'src' => $url('photo-1683194247996-43897678c94c'),
                'alt' => 'Doha skyline at night with a full moon over the Persian Gulf',
                'credit' => 'Akbar Nemati / Unsplash',
            ],
            'mia' => [
                'src' => $url('photo-1507904139316-3c7422a97a49'),
                'alt' => 'Doha skyline at blue hour seen from the Museum of Islamic Art',
                'credit' => 'Florian Wehde / Unsplash',
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
<p><strong>SOV SUMMIT runs a fully coordinated delegation programme in Doha, Qatar, during Doha Forum 2026 on 5 and 6 December 2026.</strong> We plan and manage hotels, airport meet-and-greet, chauffeured transport, private meeting rooms and on-site support, so that leaders and their teams can focus on dialogue while one team handles everything else.</p>

<h2>Doha Forum 2026 at a glance</h2>
<ul>
  <li><strong>What:</strong> Delegation management and logistics during Doha Forum 2026</li>
  <li><strong>When:</strong> 5–6 December 2026 (main programme), with partner-led events across Doha on 7 December</li>
  <li><strong>Where:</strong> Doha, Qatar — main venue: Sheraton Grand Doha</li>
  <li><strong>Theme:</strong> “Redefining Global Trust”</li>
  <li><strong>Who it is for:</strong> government and institutional delegations, diplomats, business leaders, investors and their teams</li>
  <li><strong>Coordinated by:</strong> SOV SUMMIT (Sovereign Summit GmbH), Zug, Switzerland</li>
</ul>

<h2>Why Doha Forum needs early planning</h2>
<p>Doha Forum brings thousands of leaders, officials, experts and media to the same city on the same days. The 2025 edition welcomed more than 6,500 participants from over 170 countries. Hotels close to the main venue fill early, airport arrivals peak on the eve of the Forum, and many delegations add bilateral meetings and dinners around the official programme. A delegation with confirmed rooms, cars and meeting space arrives calm and keeps its schedule.</p>

<h2>What SOV SUMMIT coordinates</h2>
<ul>
  <li><strong>Accommodation:</strong> hotel rooms and suites close to the main venue and the West Bay business district</li>
  <li><strong>Arrivals:</strong> meet-and-greet at Hamad International Airport (DOH) and fast, quiet transfers</li>
  <li><strong>Ground transport:</strong> chauffeured premium vehicles and on-call cars for the full stay</li>
  <li><strong>Private meetings:</strong> rooms for bilateral meetings, breakfasts, dinners and receptions</li>
  <li><strong>Delegation management:</strong> agendas, scheduling, guest lists and one point of contact</li>
  <li><strong>Security coordination:</strong> liaison with protection teams, secure routing and discreet arrivals</li>
</ul>

<h2>How the programme works</h2>
<ol>
  <li><strong>Briefing:</strong> we confirm goals, dates, delegation size and budget.</li>
  <li><strong>Plan:</strong> you receive one clear proposal for hotels, transport, meeting rooms and staffing.</li>
  <li><strong>Secure:</strong> we book and confirm all suppliers and share a live run-of-show.</li>
  <li><strong>Deliver:</strong> our team is on the ground in Doha from your arrival to your departure.</li>
  <li><strong>Review:</strong> after the Forum, you receive a short debrief and cost summary.</li>
</ol>

<h2>Doha and Abu Dhabi in one trip</h2>
<p>Abu Dhabi Finance Week runs from 7 to 10 December 2026, straight after Doha Forum. The flight between Doha and Abu Dhabi takes a little over an hour, so many delegations combine both. SOV SUMMIT can plan the full week as one programme, with one team and one point of contact.</p>

<p><em>SOV SUMMIT is an independent coordination company and is not affiliated with Doha Forum. Doha Forum is an invitation-only event; attendance is decided by the organisers. Our services cover logistics, hospitality and private programmes around the Forum.</em></p>
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
            ['stats', 'Doha Forum 2026 key facts', [
                'eyebrow' => 'Key facts',
                'heading' => 'Doha Forum 2026 in numbers.',
                'lede' => 'The essentials for planning a delegation in Doha this December.',
                'items' => [
                    ['number' => '5–6 Dec', 'label' => 'Doha Forum 2026 main programme'],
                    ['number' => '6,500+', 'label' => 'Participants at the 2025 edition'],
                    ['number' => '170+', 'label' => 'Countries represented in 2025'],
                    ['number' => '1 team', 'label' => 'For your whole stay in Doha'],
                ],
            ]],

            ['card_grid', 'Doha services', [
                'eyebrow' => 'What we coordinate',
                'heading' => 'One team for your entire Doha stay.',
                'lede' => 'Every element of your delegation, planned together and managed on the ground.',
                'columns' => 3,
                'cards' => [
                    ['title' => 'Accommodation', 'description' => 'Rooms and suites close to the main venue and West Bay, secured early.', 'image' => $images['westbay']['src']],
                    ['title' => 'Airport arrivals', 'description' => 'Meet-and-greet at Hamad International Airport and quiet, direct transfers.', 'image' => $images['corniche']['src']],
                    ['title' => 'Private meetings', 'description' => 'Rooms for bilaterals, breakfasts, dinners and receptions around the Forum.', 'image' => $images['mia']['src']],
                    ['title' => 'Chauffeured transport', 'description' => 'Premium vehicles and on-call cars with experienced local drivers.', 'image' => $images['night']['src']],
                    ['title' => 'Delegation management', 'description' => 'Agendas, scheduling, guest lists and one point of contact.', 'image' => $images['westbay']['src']],
                    ['title' => 'Security coordination', 'description' => 'Liaison with protection teams, secure routing and discreet arrivals.', 'image' => $images['night']['src']],
                ],
            ]],

            ['image_text', 'Arriving in Doha', [
                'eyebrow' => 'Logistics',
                'heading' => 'Land, meet, move on.',
                'lede' => 'Hamad International Airport is close to the city, so arrivals can be fast — if they are planned.',
                'body' => '<p>We plan each arrival around your first meeting. Vehicles, drivers and routes are confirmed in advance, and our team tracks flights in real time.</p><ul><li>Meet-and-greet at Hamad International Airport (DOH)</li><li>Premium vehicles for principals and staff</li><li>On-call cars throughout the Forum</li><li>Real-time coordination by our on-site team</li></ul>',
                'image' => $images['corniche']['src'],
                'image_alt' => $images['corniche']['alt'],
                'align' => 'right',
            ]],

            ['gallery', 'Doha gallery', [
                'eyebrow' => 'Doha, Qatar',
                'heading' => 'The setting.',
                'lede' => 'A modern skyline on the Gulf, a short drive from one of the world’s busiest airports.',
                'columns' => 2,
                'images' => array_map(fn (array $image): array => [
                    'src' => $image['src'],
                    'alt' => $image['alt'],
                    'caption' => $image['alt'].' — Photo: '.$image['credit'],
                ], array_values($images)),
            ]],

            ['accordion', 'Doha Forum 2026 FAQ', [
                'eyebrow' => 'FAQ',
                'heading' => 'Doha Forum 2026: common questions.',
                'lede' => 'Short answers for delegations planning their stay in Doha.',
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

            ['cta_band', 'Doha Forum 2026 call to action', [
                'eyebrow' => 'Doha Forum 2026',
                'heading' => 'Plan your Doha stay now.',
                'subheading' => 'Hotels near the main venue fill early. Share your delegation size and dates, and we will send a clear proposal.',
                'cta_label' => 'Discuss your Doha programme',
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
                'q' => 'When and where is Doha Forum 2026?',
                'a' => 'Doha Forum 2026 takes place on 5 and 6 December 2026 at the Sheraton Grand Doha in Doha, Qatar. Partner-led events continue at venues across Doha on 7 December. The theme is “Redefining Global Trust”.',
            ],
            [
                'q' => 'Do I need an invitation to attend Doha Forum?',
                'a' => 'Yes. Doha Forum is an invitation-only event, and attendance is decided by the organisers. SOV SUMMIT does not sell access to the Forum. We coordinate logistics, hospitality and private meetings for delegations around it.',
            ],
            [
                'q' => 'What does SOV SUMMIT coordinate in Doha?',
                'a' => 'SOV SUMMIT coordinates hotels, meet-and-greet at Hamad International Airport, chauffeured transport, private meeting rooms, delegation scheduling and security coordination, with one team on the ground for the full stay.',
            ],
            [
                'q' => 'Can we combine Doha Forum and Abu Dhabi Finance Week?',
                'a' => 'Yes. Abu Dhabi Finance Week runs from 7 to 10 December 2026, right after Doha Forum, and the flight between the two cities takes a little over an hour. SOV SUMMIT can plan both as one programme.',
            ],
            [
                'q' => 'When should a delegation start planning for Doha Forum 2026?',
                'a' => 'Now. Hotels close to the main venue fill early, and arrivals peak on the day before the Forum. The earlier we confirm rooms and cars, the more choice and the better the price.',
            ],
            [
                'q' => 'How do we start planning with SOV SUMMIT?',
                'a' => 'Contact SOV SUMMIT with your delegation size, travel dates and main goals. We reply with a clear proposal covering hotels, transport, meeting rooms and staffing.',
            ],
        ];
    }
};
