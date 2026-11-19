<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Service;
use App\Support\Seo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoEntityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config()->set('app.canonical_url', 'https://sov-summit.com');
        config()->set('app.url', 'https://dev.sov-summit.com');
    }

    public function test_home_organization_schema_has_entity_details_and_never_the_dev_domain(): void
    {
        config()->set('seo.profiles.linkedin', 'https://www.linkedin.com/company/sov-summit');

        $html = $this->get('/')->assertOk()->getContent();

        $this->assertStringContainsString('"@id":"https://sov-summit.com/#organization"', $html);
        $this->assertStringContainsString('"telephone":"+41 71 539 23 00"', $html);
        $this->assertStringContainsString('"contactPoint"', $html);
        $this->assertStringContainsString('"sameAs":["https://www.linkedin.com/company/sov-summit"]', $html);
        $this->assertStringContainsString('href="https://www.linkedin.com/company/sov-summit"', $html);
        $this->assertStringNotContainsString('dev.sov-summit.com', $html);
    }

    public function test_upcoming_event_schema_has_country_and_no_performer(): void
    {
        $event = Event::factory()->create([
            'is_published' => true,
            'event_date' => now()->addMonth(),
            'location' => 'Davos, Switzerland',
        ]);

        $html = $this->get(route('events.show', $event))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"Event"', $html);
        $this->assertStringContainsString('"addressLocality":"Davos","addressCountry":"Switzerland"', $html);
        $this->assertStringContainsString('"organizer":{"@type":"Organization","@id":"https://sov-summit.com/#organization"', $html);
        $this->assertStringNotContainsString('"performer"', $html);
        $this->assertStringNotContainsString('dev.sov-summit.com', $html);
    }

    public function test_past_event_is_marked_up_as_a_case_study_article(): void
    {
        $event = Event::factory()->create(['is_published' => true, 'event_date' => now()->subMonth()]);

        $html = $this->get(route('events.show', $event))->assertOk()->getContent();

        $this->assertStringContainsString('"@type":"Article"', $html);
        $this->assertStringNotContainsString('"@type":"Event"', $html);
    }

    public function test_service_schema_uses_organization_reference_and_normal_case_service_type(): void
    {
        $service = Service::factory()->create(['eyebrow' => 'DELEGATION MANAGEMENT']);

        $html = $this->get($service->publicUrl())->assertOk()->getContent();

        $this->assertStringContainsString('"serviceType":"Delegation Management"', $html);
        $this->assertStringContainsString('"provider":{"@type":"Organization","@id":"https://sov-summit.com/#organization"', $html);
        $this->assertStringNotContainsString('dev.sov-summit.com', $html);
    }

    public function test_pages_have_default_robots_meta_and_404_is_noindex_without_canonical(): void
    {
        $this->get('/about')
            ->assertOk()
            ->assertSee('<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1" />', false);

        $this->get('/this-page-does-not-exist')
            ->assertNotFound()
            ->assertSee('<meta name="robots" content="noindex, follow" />', false)
            ->assertDontSee('rel="canonical"', false);
    }

    public function test_insights_redirects_permanently_to_the_blog(): void
    {
        $this->get('/insights')->assertStatus(301)->assertRedirect('/blog');
    }

    public function test_llms_txt_lists_company_facts_and_published_services(): void
    {
        $service = Service::factory()->create(['title' => 'Delegation Management']);
        Service::factory()->unpublished()->create(['title' => 'Hidden Draft Service']);

        $this->get('/llms.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('# SOV SUMMIT')
            ->assertSee('Sovereign Summit GmbH')
            ->assertSee('+41 71 539 23 00')
            ->assertSee('[Delegation Management](https://sov-summit.com/services/'.$service->slug.')', false)
            ->assertDontSee('Hidden Draft Service');
    }

    public function test_hsts_header_is_sent_over_https_only(): void
    {
        $this->get('https://sov-summit.com/about')
            ->assertHeader('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');

        $this->get('http://sov-summit.com/about')
            ->assertHeaderMissing('Strict-Transport-Security');
    }

    public function test_meta_description_is_cut_at_a_word_boundary(): void
    {
        $long = str_repeat('Delegation logistics in Davos ', 10);

        $description = Seo::description($long);

        $this->assertLessThanOrEqual(160, mb_strlen($description));
        $this->assertStringEndsWith('…', $description);
        $this->assertSame('Short text.', Seo::description('  Short   text. '));
    }
}
