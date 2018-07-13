<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ships',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cig_id');
                $table->string('name');
                $table->unsignedInteger('manufacturer_id');
                $table->unsignedInteger('production_status_id');
                $table->unsignedInteger('vehicle_size_id');
                $table->unsignedInteger('vehicle_type_id');
                $table->float('length');
                $table->float('beam');
                $table->float('height');
                $table->string('mass');
                $table->string('cargo_capacity')->nullable();
                $table->unsignedInteger('min_crew');
                $table->unsignedInteger('max_crew');
                $table->unsignedInteger('scm_speed');
                $table->float('afterburner_speed');
                $table->float('pitch_max');
                $table->float('yaw_max');
                $table->float('roll_max');
                $table->float('xaxis_acceleration');
                $table->float('yaxis_acceleration');
                $table->float('zaxis_acceleration');
                $table->unsignedInteger('chassis_id');
                $table->timestamps();

                $table->unique('cig_id');
                $table->foreign('manufacturer_id')->references('cig_id')->on('manufacturers')->onDelete('cascade');
                $table->foreign('production_status_id')->references('id')->on('production_statuses')->onDelete(
                    'cascade'
                );
                $table->foreign('vehicle_size_id')->references('id')->on('vehicle_sizes')->onDelete('cascade');
                $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
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
        Schema::dropIfExists('ships');
    }
}
