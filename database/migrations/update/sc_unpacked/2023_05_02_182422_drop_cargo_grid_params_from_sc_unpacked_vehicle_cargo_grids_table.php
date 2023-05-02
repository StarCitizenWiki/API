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
        Schema::table('star_citizen_unpacked_vehicle_cargo_grids', static function (Blueprint $table) {
            $table->dropColumn('personal_inventory');
            $table->dropColumn('invisible');
            $table->dropColumn('mining_only');
            $table->dropColumn('min_volatile_power_to_explode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('star_citizen_unpacked_vehicle_cargo_grids', static function (Blueprint $table) {
            $table->boolean('personal_inventory');
            $table->boolean('invisible');
            $table->boolean('mining_only');
            $table->unsignedDouble('min_volatile_power_to_explode');
        });
    }
};
