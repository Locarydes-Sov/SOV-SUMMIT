<?php

namespace Tests\Feature;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpecialOfferTest extends TestCase
{
    private const BASE_URL = 'https://api.test/api/v2/external';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        config()->set('services.localrydes', [
            'base_url' => self::BASE_URL,
            'key' => 'test-key',
            'timeout' => 5,
            'verify_ssl' => true,
            'cache_ttl' => 0,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function offer(array $overrides = []): array
    {
        return array_merge([
            'id' => 42,
            'uuid' => '9d1a2b3c-uuid',
            'title' => 'Vienna City Tour',
            'shortDescription' => 'Three days in Vienna',
            'sourceCurrency' => ['isoCode' => 'EUR', 'symbol' => '€'],
            'price' => 450,
            'discount' => 50,
            'discountType' => 'fixed',
            'startDate' => '2026-05-01',
            'endDate' => '2026-09-30',
            'city' => ['name' => 'Vienna', 'countryName' => 'Austria'],
            'vehicle' => ['title' => 'Mercedes E-Class', 'seatingCapacity' => 4, 'luggageCapacity' => 3],
            'thumbnail' => 'https://cdn.test/a.jpg',
            'images' => ['https://cdn.test/a.jpg', 'https://cdn.test/b.jpg'],
            'durationUnit' => 'daily',
            'duration' => 3,
            'maxPassengerLimit' => 4,
            'contents' => [[
                'title' => 'Day 1: Schönbrunn',
                'description' => '<p onclick="steal()">Palace&nbsp;visit</p><script>alert(1)</script>',
            ]],
            'specialOfferCategory' => ['name' => 'City Tours'],
        ], $overrides);
    }

    public function test_index_lists_offers_from_the_api_with_api_key_header(): void
    {
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response([
                'success' => true,
                'data' => ['specialOffers' => [$this->offer()]],
            ]),
        ]);

        $this->get(route('special-offers.index', ['search' => 'vienna']))
            ->assertOk()
            ->assertSee('Vienna City Tour')
            ->assertSee('€400.00')
            ->assertSee('<s class="offer-price__old">€450.00</s>', false)
            ->assertSee(route('special-offers.show', ['uuid' => '9d1a2b3c-uuid', 'slug' => 'vienna-city-tour']));

        Http::assertSent(fn (Request $request): bool => $request->hasHeader('x-api-key', 'test-key')
            && $request['search_key'] === 'vienna'
            && (int) $request['limit'] === 13
            && (int) $request['skip'] === 0);
    }

    public function test_index_shows_next_page_link_when_more_offers_exist(): void
    {
        $offers = array_map(fn (int $i): array => $this->offer(['uuid' => "uuid-$i", 'title' => "Offer $i"]), range(1, 13));

        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response(['success' => true, 'data' => ['specialOffers' => $offers]]),
        ]);

        $this->get(route('special-offers.index'))
            ->assertOk()
            ->assertSee('Offer 12')
            ->assertDontSee('Offer 13')
            ->assertSee('page=2', false);
    }

    public function test_index_shows_friendly_message_when_api_fails(): void
    {
        Http::fake([
            self::BASE_URL.'/special-offers*' => Http::response(['success' => false], 500),
        ]);

        $this->get(route('special-offers.index'))
            ->assertOk()
            ->assertSee('Special offers are not available right now');
    }

    public function test_show_renders_offer_details(): void
    {
        Http::fake([
            self::BASE_URL.'/special-offers/9d1a2b3c-uuid' => Http::response([
                'success' => true,
                'data' => ['specialOffer' => $this->offer()],
            ]),
            self::BASE_URL.'/special-offers?*' => Http::response([
                'success' => true,
                'data' => ['specialOffers' => [
                    $this->offer(),
                    $this->offer(['uuid' => 'other-uuid', 'title' => 'Salzburg Day Trip']),
                ]],
            ]),
        ]);

        $this->get(route('special-offers.show', ['uuid' => '9d1a2b3c-uuid', 'slug' => 'vienna-city-tour']))
            ->assertOk()
            ->assertSee('Vienna City Tour')
            ->assertSee('Day 1: Schönbrunn')
            ->assertSee('<p>Palace visit</p>', false)
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('steal()', false)
            ->assertSee('Mercedes E-Class')
            ->assertSee('3 days')
            ->assertSee('Salzburg Day Trip')
            ->assertSee('Expand all');
    }

    public function test_show_keeps_layout_when_api_html_is_broken(): void
    {
        $broken = $this->offer(['contents' => [
            ['title' => 'Overview', 'description' => '<h3><p>Intro</p><li>Loose item</li></ol></li></div>'],
            ['title' => 'Policy', 'description' => '-'],
        ]]);

        Http::fake([
            self::BASE_URL.'/special-offers/9d1a2b3c-uuid' => Http::response(['success' => true, 'data' => ['specialOffer' => $broken]]),
            self::BASE_URL.'/special-offers?*' => Http::response(['success' => true, 'data' => ['specialOffers' => []]]),
        ]);

        $html = $this->get(route('special-offers.show', ['uuid' => '9d1a2b3c-uuid', 'slug' => 'vienna-city-tour']))
            ->assertOk()
            ->assertSee('<p>Intro</p><ul><li>Loose item</li></ul>', false)
            ->getContent();

        $this->assertSame(2, substr_count($html, '<details'));
        $this->assertSame(2, substr_count($html, '</details>'));
    }

    public function test_show_without_slug_redirects_permanently_to_the_readable_url(): void
    {
        Http::fake([
            self::BASE_URL.'/special-offers/9d1a2b3c-uuid' => Http::response(['success' => true, 'data' => ['specialOffer' => $this->offer()]]),
        ]);

        $this->get('/special-offers/9d1a2b3c-uuid')
            ->assertStatus(301)
            ->assertRedirect(route('special-offers.show', ['uuid' => '9d1a2b3c-uuid', 'slug' => 'vienna-city-tour']));

        $this->get('/special-offers/9d1a2b3c-uuid/old-title')
            ->assertStatus(301)
            ->assertRedirect(route('special-offers.show', ['uuid' => '9d1a2b3c-uuid', 'slug' => 'vienna-city-tour']));
    }

    public function test_show_returns_404_when_offer_not_found(): void
    {
        Http::fake([
            self::BASE_URL.'/special-offers/missing' => Http::response(['success' => false], 404),
        ]);

        $this->get(route('special-offers.show', 'missing'))->assertNotFound();
    }
}
