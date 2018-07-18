<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroundVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ground_vehicles',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cig_id');
                $table->string('name');
                $table->unsignedInteger('manufacturer_id');
                $table->unsignedInteger('production_status_id');
                $table->unsignedInteger('production_note_id');
                $table->unsignedInteger('vehicle_size_id');
                $table->unsignedInteger('vehicle_type_id');
                $table->float('length')->nullable();
                $table->float('beam')->nullable();
                $table->float('height')->nullable();
                $table->unsignedBigInteger('mass')->nullable();
                $table->unsignedInteger('cargo_capacity')->nullable();
                $table->unsignedInteger('min_crew')->nullable();
                $table->unsignedInteger('max_crew')->nullable();
                $table->unsignedInteger('scm_speed')->nullable();
                $table->unsignedInteger('afterburner_speed')->nullable();
                $table->unsignedInteger('chassis_id');
                $table->timestamps();

                $table->unique('cig_id');
                $table->foreign('manufacturer_id')->references('cig_id')->on('manufacturers')->onDelete('cascade');
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
        Schema::dropIfExists('ground_vehicles');
    }
}
