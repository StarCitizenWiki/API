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
        Schema::table('sc_item_flight_controllers', static function (Blueprint $table) {
            $table->unsignedDouble('scm_boost_forward')->nullable()->after('scm_speed');
            $table->unsignedDouble('scm_boost_backward')->nullable()->after('scm_boost_forward');

            $table->unsignedDouble('pitch_boost_multiplier')->nullable()->after('roll');
            $table->unsignedDouble('roll_boost_multiplier')->nullable()->after('pitch_boost_multiplier');
            $table->unsignedDouble('yaw_boost_multiplier')->nullable()->after('roll_boost_multiplier');

            $table->unsignedDouble('afterburner_capacitor')->nullable()->after('yaw_boost_multiplier');
            $table->unsignedDouble('afterburner_idle_cost')->nullable()->after('afterburner_capacitor');
            $table->unsignedDouble('afterburner_linear_cost')->nullable()->after('afterburner_idle_cost');
            $table->unsignedDouble('afterburner_angular_cost')->nullable()->after('afterburner_linear_cost');
            $table->unsignedDouble('afterburner_regen_per_sec')->nullable()->after('afterburner_angular_cost');
            $table->unsignedDouble('afterburner_regen_delay_after_use')->nullable()->after('afterburner_regen_per_sec');

            $table->unsignedDouble('afterburner_pre_delay_time')->nullable()->after('afterburner_regen_delay_after_use');
            $table->unsignedDouble('afterburner_ramp_up_time')->nullable()->after('afterburner_pre_delay_time');
            $table->unsignedDouble('afterburner_ramp_down_time')->nullable()->after('afterburner_ramp_up_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_item_flight_controllers', static function (Blueprint $table) {
            $table->dropColumn('scm_boost_forward');
            $table->dropColumn('scm_boost_backward');
            $table->dropColumn('pitch_boost_multiplier');
            $table->dropColumn('roll_boost_multiplier');
            $table->dropColumn('yaw_boost_multiplier');
            $table->dropColumn('afterburner_capacitor');
            $table->dropColumn('afterburner_idle_cost');
            $table->dropColumn('afterburner_linear_cost');
            $table->dropColumn('afterburner_angular_cost');
            $table->dropColumn('afterburner_regen_per_sec');
            $table->dropColumn('afterburner_regen_delay_after_use');
            $table->dropColumn('afterburner_pre_delay_time');
            $table->dropColumn('afterburner_ramp_up_time');
            $table->dropColumn('afterburner_ramp_down_time');
        });
    }
};
