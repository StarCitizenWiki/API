<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipQuantumDrivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_quantum_drives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();
            $table->unsignedDouble('fuel_rate');
            $table->string('jump_range');
            $table->unsignedDouble('standard_speed');
            $table->unsignedDouble('standard_cooldown');
            $table->unsignedDouble('standard_stage_1_acceleration');
            $table->unsignedDouble('standard_stage_2_acceleration');
            $table->unsignedDouble('standard_spool_time');
            $table->unsignedDouble('spline_speed');
            $table->unsignedDouble('spline_cooldown');
            $table->unsignedDouble('spline_stage_1_acceleration');
            $table->unsignedDouble('spline_stage_2_acceleration');
            $table->unsignedDouble('spline_spool_time');
            $table->timestamps();

            $table->foreign('ship_item_id', 'cooler_ship_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'quantum_drive_ship_item_uuid')
                ->references('uuid')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_ship_quantum_drives');
    }
}
