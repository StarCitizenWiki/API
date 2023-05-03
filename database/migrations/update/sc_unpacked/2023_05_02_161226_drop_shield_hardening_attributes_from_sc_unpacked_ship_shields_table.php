<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('star_citizen_unpacked_ship_shields', function (Blueprint $table) {
            $table->dropColumn('shield_hardening_factor');
        });
        Schema::table('star_citizen_unpacked_ship_shields', function (Blueprint $table) {
            $table->dropColumn('shield_hardening_duration');
        });
        Schema::table('star_citizen_unpacked_ship_shields', function (Blueprint $table) {
            $table->dropColumn('shield_hardening_cooldown');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('star_citizen_unpacked_ship_shields', function (Blueprint $table) {
            $table->unsignedDouble('shield_hardening_factor');
            $table->unsignedDouble('shield_hardening_duration');
            $table->unsignedDouble('shield_hardening_cooldown');
        });
    }
};
