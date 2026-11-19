<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * SEO audit fixes for content stored in the database (local and live have the same data).
 *
 * Rows are found by slug. Each service is read from the "services" table first, and only
 * the fields that still need the fix are updated.
 */
return new class extends Migration
{
    /**
     * New values per service slug.
     *
     * @var array<string, array<string, string>>
     */
    private const SERVICE_UPDATES = [
        // Page H1 was a slogan ("Capture the moments that matter"); use a topic heading.
        'media-coverage' => [
            'title' => 'Event photography, video and media coverage',
        ],
    ];

    /**
     * New values per event slug.
     *
     * @var array<string, array<string, string>>
     */
    private const EVENT_UPDATES = [
        // SEO title was 103 characters; about 60 is the limit Google shows.
        'sovereign-executive-forum' => [
            'seo_title' => 'Sovereign Executive Forum in Zug, Switzerland',
        ],
    ];

    public function up(): void
    {
        $this->applyUpdates('services', self::SERVICE_UPDATES);
        $this->applyUpdates('events', self::EVENT_UPDATES);
    }

    public function down(): void
    {
        // Content change only. Edit the rows in the admin panel to change them back.
    }

    /**
     * @param  array<string, array<string, string>>  $updates
     */
    private function applyUpdates(string $table, array $updates): void
    {
        foreach ($updates as $slug => $values) {
            $row = DB::table($table)->where('slug', $slug)->first();

            if ($row === null) {
                continue;
            }

            $changes = array_filter(
                $values,
                fn (string $value, string $column): bool => ($row->{$column} ?? null) !== $value,
                ARRAY_FILTER_USE_BOTH,
            );

            if ($changes !== []) {
                DB::table($table)->where('slug', $slug)->update($changes + ['updated_at' => now()]);
            }
        }
    }
};
