<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJumppointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'jumppoints',
            function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('exclude')->default(false);
                $table->integer('cig_id');
                $table->string('size');
                $table->string('direction');
                // Entry
                $table->unsignedInteger('entry_id');
                $table->string('entry_status');
                $table->unsignedInteger('entry_system_id');
                $table->string('entry_code');
                $table->string('entry_designation');
                // Exit
                $table->unsignedInteger('exit_id');
                $table->string('exit_status');
                $table->unsignedInteger('exit_system_id');
                $table->string('exit_code');
                $table->string('exit_designation');
                $table->timestamps();

                $table->unique('cig_id');
                $table->foreign('entry_id')->references('id')->on('celestial_objects');
                $table->foreign('exit_id')->references('id')->on('celestial_objects');

                $table->foreign('entry_system_id')->references('id')->on('starsystems');
                $table->foreign('exit_system_id')->references('id')->on('starsystems');
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
        Schema::dropIfExists('jumppoints');
    }
}
