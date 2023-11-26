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
        Schema::table('sc_item_missiles', static function (Blueprint $table) {
			$table->unsignedDouble('lock_range_max')->nullable()->after('lock_time');
			$table->unsignedDouble('lock_range_min')->nullable()->after('lock_time');
			$table->unsignedDouble('lock_angle')->nullable()->after('lock_time');
			$table->unsignedDouble('tracking_signal_min')->nullable()->after('lock_time');
			$table->unsignedDouble('speed')->nullable()->after('lock_time');
			$table->unsignedDouble('fuel_tank_size')->nullable()->after('lock_time');
			$table->unsignedDouble('explosion_radius_min')->nullable()->after('lock_time');
			$table->unsignedDouble('explosion_radius_max')->nullable()->after('lock_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_item_missiles', static function (Blueprint $table) {
			$table->dropColumn('lock_range_max');
			$table->dropColumn('lock_range_min');
			$table->dropColumn('lock_angle');
			$table->dropColumn('tracking_signal_min');
			$table->dropColumn('speed');
			$table->dropColumn('fuel_tank_size');
			$table->dropColumn('explosion_radius_min');
			$table->dropColumn('explosion_radius_max');
        });
    }
};
