<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('star_citizen_unpacked_vehicle_mining_lasers', static function (Blueprint $table) {
            $table->dropColumn([
                'hit_type',
                'energy_rate',
                'full_damage_range',
                'zero_damage_range',
                'heat_per_second',
                'damage',
                'consumable_slots',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('star_citizen_unpacked_vehicle_mining_lasers', static function (Blueprint $table) {
            $table->string('hit_type');
            $table->unsignedDouble('energy_rate');
            $table->unsignedDouble('full_damage_range');
            $table->unsignedDouble('zero_damage_range');
            $table->unsignedDouble('heat_per_second');
            $table->unsignedDouble('damage');
            $table->unsignedDouble('consumable_slots');
        });
    }
};
