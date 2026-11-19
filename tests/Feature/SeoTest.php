<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config()->set('app.canonical_url', 'https://sov-summit.com');
    }

    public function test_ampersand_in_page_title_is_escaped_only_once(): void
    {
        $this->get('/insights')
            ->assertOk()
            ->assertSee('<title>SOV SUMMIT Insights | Events, Travel &amp; Management</title>', false)
            ->assertDontSee('&amp;amp;', false);
    }

    public function test_event_title_ends_with_site_name_only_once(): void
    {
        $event = Event::factory()->create([
            'is_published' => true,
            'seo_title' => 'Davos 2027 Delegation Logistics | SOV SUMMIT',
        ]);

        $this->get(route('events.show', $event))
            ->assertOk()
            ->assertSee('<title>Davos 2027 Delegation Logistics | SOV SUMMIT</title>', false)
            ->assertDontSee('SOV SUMMIT | SOV SUMMIT');
    }

    public function test_canonical_and_default_og_image_use_the_canonical_domain_without_trailing_slash(): void
    {
        $this->get('/insights')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="https://sov-summit.com/insights" />', false)
            ->assertSee('<meta property="og:image" content="https://sov-summit.com/assets/img/og/sov-summit-og.jpg" />', false)
            ->assertSee('<meta property="og:image:width" content="1200" />', false);
    }

    public function test_sitemap_lists_static_pages_and_published_events_only(): void
    {
        Http::fake(['*' => Http::response(['success' => true, 'data' => ['specialOffers' => []]])]);

        $published = Event::factory()->create(['is_published' => true]);
        $draft = Event::factory()->create(['is_published' => false]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee('<loc>https://sov-summit.com/</loc>', false)
            ->assertSee('<loc>https://sov-summit.com/contact</loc>', false)
            ->assertSee('<loc>https://sov-summit.com/events/'.$published->slug.'</loc>', false)
            ->assertDontSee($draft->slug);
    }

    public function test_www_host_is_permanently_redirected_to_the_canonical_domain(): void
    {
        $this->get('https://www.sov-summit.com/events?page=2')
            ->assertStatus(301)
            ->assertRedirect('https://sov-summit.com/events?page=2');
    }

    public function test_sitemap_lists_management_training_once_and_special_offers_with_readable_urls(): void
    {
        config()->set('services.localrydes', [
            'base_url' => 'https://api.test/api/v2/external',
            'key' => 'test-key',
            'timeout' => 5,
            'verify_ssl' => true,
            'cache_ttl' => 0,
        ]);

        Http::fake([
            'https://api.test/api/v2/external/special-offers*' => Http::response(['success' => true, 'data' => ['specialOffers' => [
                ['uuid' => 'abc-123', 'title' => 'WEF Davos 2027 V-Class Chauffeur'],
            ]]]),
        ]);

        Service::factory()->managementTraining()->create();
        $service = Service::factory()->create();
        $event = Event::factory()->create(['is_published' => true]);

        $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

        $this->assertSame(1, substr_count($xml, '<loc>https://sov-summit.com/management-training</loc>'));
        $this->assertStringNotContainsString('/services/management-training', $xml);
        $this->assertStringContainsString('<loc>https://sov-summit.com/services/'.$service->slug.'</loc>', $xml);
        $this->assertStringContainsString('<loc>https://sov-summit.com/special-offers/abc-123/wef-davos-2027-v-class-chauffeur</loc>', $xml);
        $this->assertStringContainsString('<lastmod>'.$event->updated_at->toAtomString().'</lastmod>', $xml);
    }

    public function test_sitemap_still_works_when_special_offers_api_is_down(): void
    {
        Http::fake(['*' => Http::response(['success' => false], 500)]);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertSee('<loc>https://sov-summit.com/contact</loc>', false);
    }

    public function test_services_management_training_redirects_permanently_to_the_main_url(): void
    {
        Service::factory()->managementTraining()->create();

        $this->get('/services/management-training')
            ->assertStatus(301)
            ->assertRedirect(url('/management-training'));

        $this->get('/management-training')->assertOk();
    }
}
