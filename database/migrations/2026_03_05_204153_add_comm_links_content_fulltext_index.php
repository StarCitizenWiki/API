<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "CREATE INDEX comm_links_translation_en_fulltext_index ON comm_links USING GIN (to_tsvector('english', COALESCE(translation->>'en', '')))"
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX comm_links_translation_en_fulltext_index');
    }
};
