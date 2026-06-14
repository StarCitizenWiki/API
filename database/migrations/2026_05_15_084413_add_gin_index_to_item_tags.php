<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('CREATE INDEX game_item_data_tags_gin_idx ON game_item_data USING gin ((data->\'stdItem\'->\'Tags\') jsonb_path_ops)');
        DB::statement('CREATE INDEX game_item_data_required_tags_gin_idx ON game_item_data USING gin ((data->\'stdItem\'->\'RequiredTags\') jsonb_path_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX game_item_data_tags_gin_idx');
        DB::statement('DROP INDEX game_item_data_required_tags_gin_idx');
    }
};
