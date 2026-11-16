<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
