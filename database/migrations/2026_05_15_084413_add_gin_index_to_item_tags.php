<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Disable transactions for CONCURRENTLY index creation on PostgreSQL.
     */
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('CREATE INDEX CONCURRENTLY game_item_data_tags_gin_idx ON game_item_data USING gin ((data->\'stdItem\'->\'Tags\') jsonb_path_ops)');
            DB::statement('CREATE INDEX CONCURRENTLY game_item_data_required_tags_gin_idx ON game_item_data USING gin ((data->\'stdItem\'->\'RequiredTags\') jsonb_path_ops)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS game_item_data_tags_gin_idx');
            DB::statement('DROP INDEX CONCURRENTLY IF EXISTS game_item_data_required_tags_gin_idx');
        }
    }
};
