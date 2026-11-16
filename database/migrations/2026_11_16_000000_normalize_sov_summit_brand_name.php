<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rewrites old brand spellings ("Sov Summit", "SOV Summit", "Sov summit")
 * to "SOV SUMMIT" in content typed through the admin panel.
 *
 * Every text-like column (varchar, text, json …) of the content tables is
 * checked. REPLACE() is case-sensitive, so slugs, URLs and emails such as
 * "sov-summit" or "sovsummit" are left as they are.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const TABLES = ['events', 'services', 'blogs', 'pages', 'content_sections'];

    /**
     * @var list<string>
     */
    private const OLD_SPELLINGS = ['Sov Summit', 'SOV Summit', 'Sov summit'];

    private const BRAND = 'SOV SUMMIT';

    /**
     * @var list<string>
     */
    private const TEXT_TYPES = ['char', 'varchar', 'tinytext', 'text', 'mediumtext', 'longtext', 'json'];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            foreach ($this->textColumns($table) as $column) {
                $this->replaceInColumn($table, $column);
            }
        }
    }

    /**
     * The old spellings are not stored, so this cannot be undone.
     */
    public function down(): void
    {
        //
    }

    /**
     * @return list<string>
     */
    private function textColumns(string $table): array
    {
        return collect(Schema::getColumns($table))
            ->filter(fn (array $column): bool => in_array(strtolower($column['type_name']), self::TEXT_TYPES, true))
            ->pluck('name')
            ->values()
            ->all();
    }

    private function replaceInColumn(string $table, string $column): void
    {
        $grammar = DB::getQueryGrammar();
        $expression = $grammar->wrap($column);

        foreach (self::OLD_SPELLINGS as $oldSpelling) {
            $expression = 'REPLACE('.$expression.', '.DB::getPdo()->quote($oldSpelling).', '.DB::getPdo()->quote(self::BRAND).')';
        }

        DB::table($table)
            ->where(function ($query) use ($column): void {
                foreach (self::OLD_SPELLINGS as $oldSpelling) {
                    $query->orWhere($column, 'like', '%'.$oldSpelling.'%');
                }
            })
            ->update([$column => DB::raw($expression)]);
    }
};
