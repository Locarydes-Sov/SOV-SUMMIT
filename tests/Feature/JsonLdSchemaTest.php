<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class JsonLdSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function staticPages(): array
    {
        return [
            'home' => ['/'],
            'about' => ['/about'],
            'services' => ['/services'],
            'products' => ['/products'],
            'events' => ['/events'],
            'blog' => ['/blog'],
            'insights' => ['/insights'],
            'contact' => ['/contact'],
            'faq' => ['/faq'],
            'legal notice' => ['/legal-notice'],
            'privacy policy' => ['/privacy-policy'],
            'cookie policy' => ['/cookie-policy'],
            'terms' => ['/terms-conditions'],
        ];
    }

    #[DataProvider('staticPages')]
    public function test_every_json_ld_block_is_valid_and_uses_the_schema_org_context(string $path): void
    {
        $html = $this->get($path)->assertOk()->getContent();

        $this->assertJsonLdBlocksAreValid($html);
    }

    public function test_not_found_page_json_ld_is_valid(): void
    {
        $html = $this->get('/this-page-does-not-exist')->assertNotFound()->getContent();

        $this->assertJsonLdBlocksAreValid($html);
    }

    private function assertJsonLdBlocksAreValid(string $html): void
    {
        $this->assertStringNotContainsString('$__contextArgs', $html);

        preg_match_all('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);

        $this->assertNotEmpty($matches[1], 'Page has no JSON-LD blocks.');

        foreach ($matches[1] as $block) {
            $schema = json_decode($block, true);

            $this->assertIsArray($schema, 'JSON-LD block is not valid JSON: '.substr($block, 0, 120));
            $this->assertSame('https://schema.org', $schema['@context'] ?? null);
        }
    }
}
