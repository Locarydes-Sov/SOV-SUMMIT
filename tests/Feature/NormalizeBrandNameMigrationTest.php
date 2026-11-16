<?php

namespace Tests\Feature;

use App\Models\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class NormalizeBrandNameMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_old_brand_spellings_are_rewritten_and_slugs_are_kept(): void
    {
        $event = Event::factory()->create([
            'title' => 'Davos week with Sov Summit',
            'slug' => 'sov-summit-davos',
            'description' => 'SOV Summit handles the logistics. Sov summit team.',
        ]);

        DB::table('content_sections')->insert([
            'sectionable_type' => Event::class,
            'sectionable_id' => $event->id,
            'type' => 'hero',
            'data' => json_encode(['label' => 'Welcome to Sov Summit']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $migration = require database_path('migrations/2026_11_16_000000_normalize_sov_summit_brand_name.php');
        $migration->up();

        $event->refresh();
        $this->assertSame('Davos week with SOV SUMMIT', $event->title);
        $this->assertSame('SOV SUMMIT handles the logistics. SOV SUMMIT team.', $event->description);
        $this->assertSame('sov-summit-davos', $event->slug);
        $this->assertSame(
            ['label' => 'Welcome to SOV SUMMIT'],
            json_decode(DB::table('content_sections')->value('data'), true),
        );
    }
}
