<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GoogleTagManagerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_gtm_container_is_rendered_on_public_pages(): void
    {
        foreach (['/', '/about', '/contact'] as $path) {
            $this->get($path)
                ->assertOk()
                ->assertSee('https://www.googletagmanager.com/gtm.js?id=GTM-N5825MP2', false)
                ->assertSee('https://www.googletagmanager.com/ns.html?id=GTM-N5825MP2', false)
                ->assertSee("event: 'gtm.js'", false);
        }
    }
}
