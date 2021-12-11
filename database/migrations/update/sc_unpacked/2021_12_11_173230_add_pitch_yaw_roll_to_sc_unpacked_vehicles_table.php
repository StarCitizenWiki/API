<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPitchYawRollToScUnpackedVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->unsignedDouble('pitch')->nullable()->after('max_to_zero');
        });

        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->unsignedDouble('yaw')->nullable()->after('pitch');
        });

        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->unsignedDouble('roll')->nullable()->after('yaw');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->dropColumn('roll');
        });

        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->dropColumn('yaw');
        });

        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->dropColumn('pitch');
        });
    }
}
