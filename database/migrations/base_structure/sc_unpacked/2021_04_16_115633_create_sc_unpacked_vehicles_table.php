<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'star_citizen_unpacked_vehicles',
            function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('shipmatrix_id');
                $table->string('class_name');
                $table->string('name')->unique();
                $table->string('career');
                $table->string('role');
                $table->boolean('is_ship')->default(true);
                $table->unsignedInteger('size')->default(0);
                $table->unsignedInteger('cargo_capacity')->default(0);
                $table->unsignedInteger('crew')->default(0);
                $table->unsignedInteger('weapon_crew')->default(0);
                $table->unsignedInteger('operations_crew')->default(0);
                $table->unsignedBigInteger('mass')->default(0);

                $table->unsignedDouble('health_nose')->default(0);
                $table->unsignedDouble('health_body')->default(0);

                $table->unsignedDouble('scm_speed')->default(0);
                $table->unsignedDouble('max_speed')->default(0);

                $table->unsignedDouble('zero_to_scm')->default(0);
                $table->unsignedDouble('zero_to_max')->default(0);

                $table->unsignedDouble('scm_to_zero')->default(0);
                $table->unsignedDouble('max_to_zero')->default(0);

                $table->unsignedDouble('acceleration_main')->default(0);
                $table->unsignedDouble('acceleration_retro')->default(0);
                $table->unsignedDouble('acceleration_vtol')->default(0);
                $table->unsignedDouble('acceleration_maneuvering')->default(0);

                $table->unsignedDouble('acceleration_g_main')->default(0);
                $table->unsignedDouble('acceleration_g_retro')->default(0);
                $table->unsignedDouble('acceleration_g_vtol')->default(0);
                $table->unsignedDouble('acceleration_g_maneuvering')->default(0);

                $table->unsignedDouble('fuel_capacity')->default(0);
                $table->unsignedDouble('fuel_intake_rate')->default(0);
                $table->unsignedDouble('fuel_usage_main')->default(0);
                $table->unsignedDouble('fuel_usage_retro')->default(0);
                $table->unsignedDouble('fuel_usage_vtol')->default(0);
                $table->unsignedDouble('fuel_usage_maneuvering')->default(0);

                $table->unsignedDouble('quantum_speed')->default(0);
                $table->unsignedDouble('quantum_spool_time')->default(0);
                $table->unsignedDouble('quantum_fuel_capacity')->default(0);
                $table->unsignedDouble('quantum_range')->default(0);

                $table->unsignedDouble('claim_time')->default(0);
                $table->unsignedDouble('expedite_time')->default(0);
                $table->unsignedDouble('expedite_cost')->default(0);
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_vehicles');
    }
}
