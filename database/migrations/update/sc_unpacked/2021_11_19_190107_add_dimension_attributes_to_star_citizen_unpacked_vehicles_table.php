<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDimensionAttributesToStarCitizenUnpackedVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->unsignedDouble('width')->nullable() ->after('size');
            $table->unsignedDouble('height')->nullable() ->after('width');
            $table->unsignedDouble('length')->nullable() ->after('height');
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
            $table->dropColumn('width');
            $table->dropColumn('height');
            $table->dropColumn('length');
        });
    }
}
