<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * Single source for the FAQ: the short list on the home page, the full /faq page
 * and both FAQPage JSON-LD blocks all read from here.
 */
class Faq
{
    /**
     * @return Collection<int, array{question: string, group: string, featured: bool, answer: string}>
     */
    public static function all(): Collection
    {
        return collect([
            [
                'question' => 'What does Sov Summit do?',
                'group' => 'About SOV Summit',
                'featured' => true,
                'answer' => <<<'HTML'
<p>Sov Summit provides end-to-end event and delegation coordination. We help clients source, compare, coordinate, and supervise services such as chauffeur transportation, logistics, accommodation, event support, and other specialist services.</p>
HTML,
            ],
            [
                'question' => 'How can Sov Summit help with chauffeur services?',
                'group' => 'Services',
                'featured' => false,
                'answer' => <<<'HTML'
<p>We identify suitable chauffeur providers based on your requirements, including vehicle category, passenger numbers, language requirements, itinerary, service level, and budget.</p>
<p>We compare available options, negotiate competitive rates, coordinate the booking, and supervise the service.</p>
HTML,
            ],
            [
                'question' => 'Can Sov Summit arrange accommodation?',
                'group' => 'Services',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Yes. We can source and coordinate hotels and other accommodation based on your preferred location, category, room requirements, budget, and guest profile.</p>
HTML,
            ],
            [
                'question' => 'Can you manage the entire transportation and logistics operation?',
                'group' => 'Services',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Yes. We can coordinate multiple vehicles, drivers, routes, schedules, airport transfers, meet &amp; greet, group movements, flight information, and on-site transportation requirements.</p>
HTML,
            ],
            [
                'question' => 'How do you select your vendors?',
                'group' => 'Vendors & pricing',
                'featured' => true,
                'answer' => <<<'HTML'
<p>We select providers according to the specific requirements of each project. Depending on the service, we assess factors such as experience, licensing, availability, service capability, reputation, and other relevant credentials.</p>
<p>Where appropriate, we also conduct background and compliance checks.</p>
HTML,
            ],
            [
                'question' => 'Do you work with your own vendors?',
                'group' => 'Vendors & pricing',
                'featured' => false,
                'answer' => <<<'HTML'
<p>We work with both established partners and external providers. Existing partners can be used where they meet the requirements, while additional providers can be sourced when a project requires specific capabilities, locations, or capacity.</p>
HTML,
            ],
            [
                'question' => 'Can you negotiate better rates?',
                'group' => 'Vendors & pricing',
                'featured' => false,
                'answer' => <<<'HTML'
<p>We use our network and purchasing volume to obtain competitive offers. Where multiple suitable providers are available, we can compare proposals and negotiate commercial terms on the client&rsquo;s behalf.</p>
HTML,
            ],
            [
                'question' => 'Will I know what I am paying for?',
                'group' => 'Vendors & pricing',
                'featured' => true,
                'answer' => <<<'HTML'
<p>Yes. We aim to provide clear and transparent proposals, with the services, quantities, pricing, and relevant conditions clearly defined.</p>
HTML,
            ],
            [
                'question' => 'Why shouldn’t I contact the vendors directly?',
                'group' => 'About SOV Summit',
                'featured' => false,
                'answer' => <<<'HTML'
<p>You can, but managing multiple vendors can become time-consuming and complicated.</p>
<p>Sov Summit provides a single coordination layer between you and the different suppliers, helping manage communication, scheduling, pricing, documentation, and execution.</p>
HTML,
            ],
            [
                'question' => 'Who supervises the services?',
                'group' => 'Delivery & technology',
                'featured' => true,
                'answer' => <<<'HTML'
<p>Depending on the project scope, Sov Summit can provide dedicated coordination and operational supervision throughout the event or delegation.</p>
HTML,
            ],
            [
                'question' => 'What happens if a vendor fails to deliver?',
                'group' => 'Delivery & technology',
                'featured' => false,
                'answer' => <<<'HTML'
<p>We work to identify reliable providers and maintain operational oversight. Where applicable and contractually agreed, Sov Summit can provide service guarantees or remedies for qualifying service failures.</p>
HTML,
            ],
            [
                'question' => 'Do you provide a mobile app or platform?',
                'group' => 'Delivery & technology',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Sov Summit is designed to organize services through a central digital platform, giving clients an easier way to manage vendors, services, schedules, and operational information.</p>
HTML,
            ],
            [
                'question' => 'Can I see all the vendors and services in one place?',
                'group' => 'Delivery & technology',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Yes. The objective is to bring fragmented services into one organized operational environment, making it easier to understand what has been booked, who is responsible, when the service takes place, and what has been agreed.</p>
HTML,
            ],
            [
                'question' => 'Can Sov Summit handle VIP or diplomatic delegations?',
                'group' => 'Services',
                'featured' => true,
                'answer' => <<<'HTML'
<p>Yes. We can coordinate requirements for VIP, corporate, diplomatic, executive, and other high-profile delegations, subject to the specific requirements and applicable regulations.</p>
HTML,
            ],
            [
                'question' => 'Can you coordinate services in different countries?',
                'group' => 'Services',
                'featured' => true,
                'answer' => <<<'HTML'
<p>Yes. Sov Summit can source and coordinate services across multiple destinations through its network of local and international partners.</p>
HTML,
            ],
            [
                'question' => 'Do you provide security services?',
                'group' => 'Services',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Sov Summit can coordinate appropriate third-party security or related services where required. We do not present ourselves as a licensed security provider unless the relevant service is provided by an appropriately licensed partner.</p>
HTML,
            ],
            [
                'question' => 'How far in advance should I contact Sov Summit?',
                'group' => 'Getting started',
                'featured' => true,
                'answer' => <<<'HTML'
<p>The earlier you contact us, the more options we generally have for sourcing, negotiation, verification, and operational planning. However, we can also assist with urgent and short-notice requirements subject to availability.</p>
HTML,
            ],
            [
                'question' => 'Can I request only one service?',
                'group' => 'Services',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Yes. You can use Sov Summit for a single requirement &mdash; such as chauffeur transportation or accommodation &mdash; or for complete event and delegation coordination.</p>
HTML,
            ],
            [
                'question' => 'What information do you need to prepare a proposal?',
                'group' => 'Getting started',
                'featured' => true,
                'answer' => <<<'HTML'
<p>Typically, we need:</p>
<ul>
<li>Event or travel dates</li>
<li>Destination(s)</li>
<li>Number of guests</li>
<li>Required services</li>
<li>Itinerary or schedule</li>
<li>Vehicle requirements</li>
<li>Accommodation requirements</li>
<li>Preferred service level</li>
<li>Any special requirements</li>
<li>Budget, if available</li>
</ul>
HTML,
            ],
            [
                'question' => 'What is the main advantage of using Sov Summit?',
                'group' => 'About SOV Summit',
                'featured' => false,
                'answer' => <<<'HTML'
<p>The event-services market is fragmented. Instead of managing numerous suppliers yourself, Sov Summit brings the relevant services together into one coordinated solution.</p>
<p><strong>We source. We verify. We negotiate. We coordinate. We supervise.</strong></p>
HTML,
            ],
            [
                'question' => 'What makes Sov Summit different?',
                'group' => 'About SOV Summit',
                'featured' => false,
                'answer' => <<<'HTML'
<p>Sov Summit combines specialist advisors, a vendor network, commercial sourcing, operational coordination, and technology.</p>
<p>Our objective is simple: <em>give you one place to organize multiple services &mdash; with transparency, control, and professional execution.</em></p>
HTML,
            ],
        ]);
    }

    /**
     * The 8 questions shown on the home page.
     *
     * @return Collection<int, array{question: string, group: string, featured: bool, answer: string}>
     */
    public static function featured(): Collection
    {
        return self::all()->where('featured', true)->values();
    }

    /**
     * schema.org FAQPage data for the given questions (answers as plain text).
     *
     * @param  Collection<int, array{question: string, group: string, featured: bool, answer: string}>  $items
     * @return array<string, mixed>
     */
    public static function schema(Collection $items): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $items->map(fn (array $item): array => [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags(str_replace(['</p>', '</li>'], ['</p> ', '</li> '], $item['answer'])), ENT_QUOTES | ENT_HTML5, 'UTF-8')) ?? ''),
                ],
            ])->values()->all(),
        ];
    }
}
