<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Adds the "Abu Dhabi Finance Week 2026" executive programme with SEO fields
 * and AI-friendly content sections: key facts, services, logistics, gallery,
 * FAQ (+ FAQPage JSON-LD) and a call to action.
 *
 * Dates and venue: Abu Dhabi Media Office / ADGM — 5th Abu Dhabi Finance Week,
 * 7–10 December 2026, hosted by ADGM on Al Maryah Island. Theme
 * "The Capital Community, Powered by Partnerships"; 800+ speakers,
 * 35,000+ participants, 70+ events.
 *
 * Photos: Unsplash (free Unsplash License), all tagged Abu Dhabi, UAE.
 */
return new class extends Migration
{
    private const SLUG = 'abu-dhabi-finance-week-2026-executive-programme';

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
                'title' => 'Abu Dhabi Finance Week 2026: Executive Programme on Al Maryah Island',
                'slug' => self::SLUG,
                'seo_title' => 'ADFW 2026 Executive Logistics in Abu Dhabi | SOV SUMMIT',
                'summary' => 'SOV SUMMIT coordinates executive and investor delegations in Abu Dhabi during Abu Dhabi Finance Week, 7–10 December 2026: hotels, airport meet-and-greet, chauffeured transport, private meeting rooms and on-site support around Al Maryah Island — planned and run by one team.',
                'seo_keywords' => 'Abu Dhabi Finance Week 2026, ADFW 2026, ADGM, Al Maryah Island, Abu Dhabi delegation management, Abu Dhabi executive travel, investor meetings Abu Dhabi, Zayed International Airport meet and greet, Abu Dhabi chauffeur service, private meeting rooms Abu Dhabi, SOV SUMMIT',
                'description' => $this->description(),
                'cover_image' => $images['skyline']['src'],
                'event_date' => '2026-12-07 09:00:00',
                'location' => 'Abu Dhabi, United Arab Emirates',
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
            'skyline' => [
                'src' => $url('photo-1603565095944-2a6f33bb517c', 1920),
                'alt' => 'Skyscrapers of the Abu Dhabi skyline across the sea, United Arab Emirates',
                'credit' => 'Nick Fewings / Unsplash',
            ],
            'panorama' => [
                'src' => $url('photo-1620243508824-c391c41a52bd'),
                'alt' => 'Panoramic view of tall skyscrapers in Abu Dhabi',
                'credit' => 'Giovanni Gagliardi / Unsplash',
            ],
            'etihad' => [
                'src' => $url('photo-1650826123559-cc1dad9c42a3'),
                'alt' => 'Abu Dhabi skyline with the Etihad Towers seen from the water',
                'credit' => 'Devarya Ruparelia / Unsplash',
            ],
            'marina' => [
                'src' => $url('photo-1590140568849-f27028e1f822'),
                'alt' => 'Abu Dhabi skyline seen across the water from Al Marina',
                'credit' => 'Belinda Fewings / Unsplash',
            ],
            'mosque' => [
                'src' => $url('photo-1724433182983-6e42de9c6e61'),
                'alt' => 'White domes and arches of the Sheikh Zayed Grand Mosque in Abu Dhabi',
                'credit' => 'Yogi Trivedi / Unsplash',
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
<p><strong>SOV SUMMIT runs a fully coordinated executive programme in Abu Dhabi, United Arab Emirates, during Abu Dhabi Finance Week from 7 to 10 December 2026.</strong> We plan and manage hotels, airport meet-and-greet, chauffeured transport, private meeting rooms and on-site support around Al Maryah Island, so that executives and investors can focus on their meetings while one team handles everything else.</p>

<h2>Abu Dhabi Finance Week 2026 at a glance</h2>
<ul>
  <li><strong>What:</strong> Executive and investor delegation logistics during Abu Dhabi Finance Week (ADFW)</li>
  <li><strong>When:</strong> 7–10 December 2026</li>
  <li><strong>Where:</strong> Al Maryah Island, Abu Dhabi — hosted by Abu Dhabi Global Market (ADGM)</li>
  <li><strong>Theme:</strong> “The Capital Community, Powered by Partnerships”</li>
  <li><strong>Scale:</strong> more than 800 speakers, over 35,000 participants and more than 70 events</li>
  <li><strong>Who it is for:</strong> asset managers, sovereign and family office teams, bankers, founders, policymakers and their staff</li>
  <li><strong>Coordinated by:</strong> SOV SUMMIT (Sovereign Summit GmbH), Zug, Switzerland</li>
</ul>

<h2>Why ADFW week needs early planning</h2>
<p>Abu Dhabi Finance Week fills Al Maryah Island and the surrounding hotels with tens of thousands of financial leaders over four days. For 2026, the organisers plan to hold about half of the sessions behind closed doors, so the week is built around private meetings as much as public stages. Delegations that arrive with confirmed rooms, cars and meeting space can say yes to more meetings — and move between them on time.</p>

<h2>What SOV SUMMIT coordinates</h2>
<ul>
  <li><strong>Accommodation:</strong> hotel rooms and suites on and near Al Maryah Island</li>
  <li><strong>Arrivals:</strong> meet-and-greet at Zayed International Airport (AUH) and direct transfers</li>
  <li><strong>Ground transport:</strong> chauffeured premium vehicles and on-call cars between meetings</li>
  <li><strong>Private meetings:</strong> rooms for investor meetings, breakfasts, dinners and receptions</li>
  <li><strong>Delegation management:</strong> agendas, scheduling, guest lists and one point of contact</li>
  <li><strong>Security coordination:</strong> liaison with protection teams, secure routing and discreet arrivals</li>
</ul>

<h2>How the programme works</h2>
<ol>
  <li><strong>Briefing:</strong> we confirm goals, dates, delegation size and budget.</li>
  <li><strong>Plan:</strong> you receive one clear proposal for hotels, transport, meeting rooms and staffing.</li>
  <li><strong>Secure:</strong> we book and confirm all suppliers and share a live run-of-show.</li>
  <li><strong>Deliver:</strong> our team is on the ground in Abu Dhabi for the full week.</li>
  <li><strong>Review:</strong> after ADFW, you receive a short debrief and cost summary.</li>
</ol>

<h2>Doha and Abu Dhabi in one trip</h2>
<p>Doha Forum takes place on 5 and 6 December 2026, just before Abu Dhabi Finance Week. The flight between Doha and Abu Dhabi takes a little over an hour, so many delegations attend both. SOV SUMMIT can plan the full week as one programme, with one team and one point of contact.</p>

<p><em>SOV SUMMIT is an independent coordination company and is not affiliated with Abu Dhabi Finance Week or ADGM. Access to ADFW sessions requires registration with the organisers. Our services cover logistics, hospitality and private programmes around the week.</em></p>
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
            ['stats', 'ADFW 2026 key facts', [
                'eyebrow' => 'Key facts',
                'heading' => 'Abu Dhabi Finance Week 2026 in numbers.',
                'lede' => 'The essentials for planning an executive programme in Abu Dhabi this December.',
                'items' => [
                    ['number' => '7–10 Dec', 'label' => 'Abu Dhabi Finance Week 2026'],
                    ['number' => '35,000+', 'label' => 'Participants expected'],
                    ['number' => '800+', 'label' => 'Speakers'],
                    ['number' => '70+', 'label' => 'Events across the week'],
                ],
            ]],

            ['card_grid', 'Abu Dhabi services', [
                'eyebrow' => 'What we coordinate',
                'heading' => 'One team for your entire ADFW week.',
                'lede' => 'Every element of your programme, planned together and managed on the ground.',
                'columns' => 3,
                'cards' => [
                    ['title' => 'Accommodation', 'description' => 'Rooms and suites on and near Al Maryah Island, secured early.', 'image' => $images['marina']['src']],
                    ['title' => 'Airport arrivals', 'description' => 'Meet-and-greet at Zayed International Airport and direct transfers.', 'image' => $images['skyline']['src']],
                    ['title' => 'Private meetings', 'description' => 'Rooms for investor meetings, breakfasts, dinners and receptions.', 'image' => $images['panorama']['src']],
                    ['title' => 'Chauffeured transport', 'description' => 'Premium vehicles and on-call cars between your meetings.', 'image' => $images['etihad']['src']],
                    ['title' => 'Delegation management', 'description' => 'Agendas, scheduling, guest lists and one point of contact.', 'image' => $images['panorama']['src']],
                    ['title' => 'Cultural programme', 'description' => 'Private visits, such as the Sheikh Zayed Grand Mosque, for guests and partners.', 'image' => $images['mosque']['src']],
                ],
            ]],

            ['image_text', 'Moving around Abu Dhabi', [
                'eyebrow' => 'Logistics',
                'heading' => 'More meetings. Less waiting.',
                'lede' => 'During ADFW, the time between meetings decides how much you get done.',
                'body' => '<p>We plan each day around your meeting list. Cars wait where you need them, and our team adjusts routes when schedules change.</p><ul><li>Meet-and-greet at Zayed International Airport (AUH)</li><li>Premium vehicles for principals and staff</li><li>On-call cars throughout the week</li><li>Real-time coordination by our on-site team</li></ul>',
                'image' => $images['etihad']['src'],
                'image_alt' => $images['etihad']['alt'],
                'align' => 'right',
            ]],

            ['gallery', 'Abu Dhabi gallery', [
                'eyebrow' => 'Abu Dhabi, UAE',
                'heading' => 'The setting.',
                'lede' => 'A capital on the water, where global finance meets Gulf hospitality.',
                'columns' => 3,
                'images' => array_map(fn (array $image): array => [
                    'src' => $image['src'],
                    'alt' => $image['alt'],
                    'caption' => $image['alt'].' — Photo: '.$image['credit'],
                ], array_values($images)),
            ]],

            ['accordion', 'ADFW 2026 FAQ', [
                'eyebrow' => 'FAQ',
                'heading' => 'Abu Dhabi Finance Week 2026: common questions.',
                'lede' => 'Short answers for executives planning their week in Abu Dhabi.',
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

            ['cta_band', 'ADFW 2026 call to action', [
                'eyebrow' => 'Abu Dhabi Finance Week 2026',
                'heading' => 'Plan your ADFW week now.',
                'subheading' => 'Hotels near Al Maryah Island fill early. Share your team size and dates, and we will send a clear proposal.',
                'cta_label' => 'Discuss your Abu Dhabi programme',
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
                'q' => 'When and where is Abu Dhabi Finance Week 2026?',
                'a' => 'Abu Dhabi Finance Week 2026 takes place from 7 to 10 December 2026 on Al Maryah Island in Abu Dhabi. It is hosted by Abu Dhabi Global Market (ADGM) under the theme “The Capital Community, Powered by Partnerships”.',
            ],
            [
                'q' => 'How big is Abu Dhabi Finance Week 2026?',
                'a' => 'The organisers expect more than 35,000 participants, over 800 speakers and more than 70 events across the four days.',
            ],
            [
                'q' => 'Does SOV SUMMIT sell tickets to ADFW?',
                'a' => 'No. Access to ADFW sessions requires registration with the organisers. SOV SUMMIT coordinates logistics, hospitality and private meetings for executives and delegations around the week.',
            ],
            [
                'q' => 'What does SOV SUMMIT coordinate in Abu Dhabi?',
                'a' => 'SOV SUMMIT coordinates hotels, meet-and-greet at Zayed International Airport, chauffeured transport, private meeting rooms, delegation scheduling and security coordination, with one team on the ground for the full week.',
            ],
            [
                'q' => 'Can we combine Abu Dhabi Finance Week with Doha Forum?',
                'a' => 'Yes. Doha Forum takes place on 5 and 6 December 2026, just before ADFW, and the flight between Doha and Abu Dhabi takes a little over an hour. SOV SUMMIT can plan both as one programme.',
            ],
            [
                'q' => 'How do we start planning with SOV SUMMIT?',
                'a' => 'Contact SOV SUMMIT with your team size, travel dates and main goals for the week. We reply with a clear proposal covering hotels, transport, meeting rooms and staffing.',
            ],
        ];
    }
};
