<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_vehicles', static function (Blueprint $table) {
            $table->json('translation')->nullable();
        });

        Schema::table('game_missions', static function (Blueprint $table) {
            $table->json('translation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_missions', static function (Blueprint $table) {
            $table->dropColumn('translation');
        });

        Schema::table('game_vehicles', static function (Blueprint $table) {
            $table->dropColumn('translation');
        });
    }
};
