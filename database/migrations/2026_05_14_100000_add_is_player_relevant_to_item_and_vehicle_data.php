<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->boolean('is_player_relevant')->default(true)->after('rarity');
            $table->index('is_player_relevant', 'gid_is_player_relevant_idx');
        });

        Schema::table('game_vehicle_data', static function (Blueprint $table): void {
            $table->boolean('is_player_relevant')->default(true)->after('uex_rental_prices');
            $table->index('is_player_relevant', 'gvd_is_player_relevant_idx');
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->dropIndex('gid_is_player_relevant_idx');
            $table->dropColumn('is_player_relevant');
        });

        Schema::table('game_vehicle_data', static function (Blueprint $table): void {
            $table->dropIndex('gvd_is_player_relevant_idx');
            $table->dropColumn('is_player_relevant');
        });
    }
};
