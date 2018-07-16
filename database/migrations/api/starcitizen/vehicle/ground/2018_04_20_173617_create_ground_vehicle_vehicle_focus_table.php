<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroundVehicleVehicleFocusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ground_vehicle_vehicle_focus',
            function (Blueprint $table) {
                $table->unsignedInteger('ground_vehicle_id');
                $table->unsignedInteger('vehicle_focus_id');

                $table->primary(['ground_vehicle_id', 'vehicle_focus_id']);
                $table->foreign('ground_vehicle_id')->references('id')->on('ground_vehicles')->onDelete('cascade');
                $table->foreign('vehicle_focus_id')->references('id')->on('vehicle_foci')->onDelete('cascade');
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
        Schema::dropIfExists('ground_vehicle_vehicle_focus');
    }
}
