<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'vehicles',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cig_id');
                $table->string('name');
                $table->unsignedInteger('manufacturer_id');
                $table->unsignedInteger('production_status_id');
                $table->unsignedInteger('production_note_id');
                $table->unsignedInteger('vehicle_size_id');
                $table->unsignedInteger('vehicle_type_id');
                $table->unsignedDecimal('length')->nullable();
                $table->unsignedDecimal('beam')->nullable();
                $table->unsignedDecimal('height')->nullable();
                $table->unsignedBigInteger('mass')->nullable();
                $table->unsignedInteger('cargo_capacity')->nullable();
                $table->unsignedInteger('min_crew')->nullable();
                $table->unsignedInteger('max_crew')->nullable();
                $table->unsignedInteger('scm_speed')->nullable();
                $table->unsignedInteger('afterburner_speed')->nullable();
                $table->unsignedDecimal('pitch_max')->nullable();
                $table->unsignedDecimal('yaw_max')->nullable();
                $table->unsignedDecimal('roll_max')->nullable();
                $table->unsignedDecimal('x_axis_acceleration')->nullable();
                $table->unsignedDecimal('y_axis_acceleration')->nullable();
                $table->unsignedDecimal('z_axis_acceleration')->nullable();
                $table->unsignedInteger('chassis_id');
                $table->timestamps();

                $table->unique('cig_id');
                $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
                $table->foreign('production_status_id')->references('id')->on('production_statuses')->onDelete(
                    'cascade'
                );
                $table->foreign('production_note_id')->references('id')->on('production_notes')->onDelete(
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
