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
        Schema::table('sc_vehicles', static function (Blueprint $table) {
            $table->index('name');
            $table->index('item_uuid');
        });

        Schema::table('sc_vehicle_hardpoints', static function (Blueprint $table) {
            $table->index('equipped_item_uuid');
        });

        Schema::table('sc_item_port_tag', static function (Blueprint $table) {
            $table->index('item_port_id');
            $table->index('is_required_tag');
        });

        Schema::table('sc_item_tag', static function (Blueprint $table) {
            $table->index('item_id');
            $table->index('is_required_tag');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_vehicles', static function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['item_uuid']);
        });

        Schema::table('sc_vehicle_hardpoints', static function (Blueprint $table) {
            $table->dropIndex(['equipped_item_uuid']);
        });

        Schema::table('sc_item_port_tag', static function (Blueprint $table) {
            $table->dropIndex(['item_port_id']);
            $table->dropIndex(['is_required_tag']);
        });

        Schema::table('sc_item_tag', static function (Blueprint $table) {
            $table->dropIndex(['item_id']);
            $table->dropIndex(['is_required_tag']);
        });
    }
};
