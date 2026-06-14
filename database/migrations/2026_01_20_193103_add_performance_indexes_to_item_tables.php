<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', function (Blueprint $table): void {
            $table->index('name');
            $table->index('class_name');
            $table->index('classification');
            $table->index(['type', 'sub_type']);
            $table->index(['manufacturer_id', 'type']);
            $table->index(['game_version_id', 'class_name']);
            $table->index(['base_id', 'game_version_id']);
            $table->index(['base_id', 'game_version_id', 'name']);
        });

        Schema::table('game_manufacturers', function (Blueprint $table): void {
            $table->index('name');
        });

        Schema::table('game_vehicle_data', function (Blueprint $table): void {
            $table->index('name');
            $table->index('display_name');
            $table->index('class_name');
            $table->index(['manufacturer_id', 'size']);
            $table->index(['career', 'role']);
        });
    }

    public function down(): void
    {
        Schema::table('game_vehicle_data', function (Blueprint $table): void {
            $table->dropIndex(['career', 'role']);
            $table->dropIndex(['manufacturer_id', 'size']);
            $table->dropIndex('class_name');
            $table->dropIndex('display_name');
            $table->dropIndex('name');
        });

        Schema::table('game_manufacturers', function (Blueprint $table): void {
            $table->dropIndex('name');
        });

        Schema::table('game_item_data', function (Blueprint $table): void {
            $table->dropIndex(['base_id', 'game_version_id', 'name']);
            $table->dropIndex(['base_id', 'game_version_id']);
            $table->dropIndex(['game_version_id', 'class_name']);
            $table->dropIndex(['manufacturer_id', 'type']);
            $table->dropIndex(['type', 'sub_type']);
            $table->dropIndex('classification');
            $table->dropIndex('class_name');
            $table->dropIndex('name');
        });
    }
};
