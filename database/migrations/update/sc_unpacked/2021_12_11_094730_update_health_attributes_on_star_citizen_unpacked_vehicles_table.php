<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateHealthAttributesOnStarCitizenUnpackedVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->dropColumn('health_body');
            $table->dropColumn('health_nose');
            $table->unsignedDouble('health')->nullable() ->after('mass');
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
            $table->dropColumn('health');
            $table->unsignedDouble('health_nose')->nullable()->after('mass');
            $table->unsignedDouble('health_body')->nullable()->after('mass');
        });
    }
}
