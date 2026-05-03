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
        Schema::table('game_item_data', function (Blueprint $table) {
            $table->unique(['item_id', 'game_version_id'], 'item_data_version_unique');
        });

        Schema::table('game_item_description_data', function (Blueprint $table) {
            $table->unique(['item_id', 'name'], 'item_description_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_item_description_data', function (Blueprint $table) {
            $table->dropUnique('item_description_name_unique');
        });

        Schema::table('game_item_data', function (Blueprint $table) {
            $table->dropUnique('item_data_version_unique');
        });
    }
};
