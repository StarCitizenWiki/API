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
        Schema::create('star_citizen_unpacked_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name')->unique();
            $table->string('career');
            $table->string('role');
            $table->unsignedDouble('cargo');
            $table->unsignedInteger('crew')->default(0);
            $table->unsignedInteger('weapon_crew')->default(0);
            $table->unsignedInteger('operations_crew')->default(0);
            $table->unsignedDouble('mass');
            $table->string('manufacturer');
            $table->unsignedDouble('insurance_claim_time');
            $table->unsignedDouble('insurance_expedite_claim_time');
            $table->unsignedDouble('insurance_expedite_cost');
            $table->timestamps();
        });
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
