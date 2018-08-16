<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJumppointTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jumppoint', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('exclude')->default(false);
            $table->timestamps();
            $table->integer('cig_id');
            $table->string('size');
            $table->string('direction');
            // Entry
            $table->integer('entry_cig_id');
            $table->string('entry_status');
            $table->integer('entry_cig_system_id');
            $table->string('entry_code');
            $table->string('entry_designation');
            // Exit
            $table->integer('exit_cig_id');
            $table->string('exit_status');
            $table->integer('exit_cig_system_id');
            $table->string('exit_code');
            $table->string('exit_designation');

            $table->unique('cig_id');
            $table->foreign('entry_cig_id')->references('cig_id')->on('celestial_object')->onDelete('cascade');
            $table->foreign('exit_cig_id')->references('cig_id')->on('celestial_object')->onDelete('cascade');

            $table->foreign('entry_cig_system_id')->references('cig_id')->on('starsystem')->onDelete('cascade');
            $table->foreign('exit_cig_system_id')->references('cig_id')->on('starsystem')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //also remove old jumppoints
        Schema::dropIfExists('jumppoints');
        Schema::dropIfExists('jumppoint');
    }
}
