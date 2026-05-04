<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add a regular column for item rarity, populated during import.
     */
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->text('rarity')->nullable();
        });

        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->index(['game_version_id', 'rarity'], 'game_item_data_version_rarity_idx');
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->dropIndex('game_item_data_version_rarity_idx');
            $table->dropColumn('rarity');
        });
    }
};
