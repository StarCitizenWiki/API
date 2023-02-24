<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleVehicleFocusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'vehicle_vehicle_focus',
            function (Blueprint $table) {
                $table->unsignedInteger('vehicle_id');
                $table->unsignedInteger('focus_id');

                $table->primary(['vehicle_id', 'focus_id'], 'vehicle_focus_primary');
                $table->foreign('vehicle_id')->references('id')->on('vehicles')->onDelete('cascade');
                $table->foreign('focus_id')->references('id')->on('vehicle_foci')->onDelete('cascade');
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
        Schema::dropIfExists('vehicle_vehicle_focus');
    }
}
