<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->boolean('is_bespoke')->default(false)->nullable(false);
            $table->jsonb('bespoke_vehicle_tags')->nullable();

            $table->index(['game_version_id', 'is_bespoke'], 'game_item_data_version_bespoke_idx');
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex('game_item_data_version_bespoke_idx');
            $table->dropColumn(['is_bespoke', 'bespoke_vehicle_tags']);
        });
    }
};
