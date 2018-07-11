<?php declare(strict_types = 1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShipsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ships', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('cig_id');
            $table->string('name');
            $table->text('description');
            $table->integer('production_status_id');
            $table->string('production_note')->nullable();
            $table->float('length');
            $table->float('beam');
            $table->float('height');
            $table->integer('size_id');
            $table->string('mass');
            $table->integer('type_id');
            $table->string('cargocapacity')->nullable();
            $table->integer('min_crew');
            $table->integer('max_crew');
            $table->integer('scm_speed');
            $table->integer('afterburnder_speed');
            $table->float('pitch_max');
            $table->float('yaw_max');
            $table->float('roll_max');
            $table->float('xaxis_acceleration');
            $table->float('yaxis_acceleration');
            $table->float('zaxis_acceleration');
            $table->integer('manufacturer_id');
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
        Schema::dropIfExists('ships');
    }
}
