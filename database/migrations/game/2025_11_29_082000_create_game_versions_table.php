<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('game_versions')) {
            // Existing deployment
            DB::table('migrations')
                ->where('migration', '2025_12_06_173538_create_game_versions_table')
                ->delete();

            return;
        }

        Schema::create('game_versions', static function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('channel')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_versions');
    }
};
