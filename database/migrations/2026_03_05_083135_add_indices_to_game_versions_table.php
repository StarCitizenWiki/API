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
            DB::statement('CREATE INDEX CONCURRENTLY game_versions_is_default_index ON game_versions (is_default) WHERE is_default = true');
        } else {
            DB::statement('CREATE INDEX game_versions_is_default_index ON game_versions (is_default)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX CONCURRENTLY game_versions_is_default_index');
        } else {
            DB::statement('DROP INDEX game_versions_is_default_index');
        }
    }
};
