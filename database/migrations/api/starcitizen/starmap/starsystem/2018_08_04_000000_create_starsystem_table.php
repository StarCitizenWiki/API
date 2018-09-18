<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStarsystemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('starsystem', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('code', 20)->unique();
            $table->boolean('exclude')->default(false);
            $table->timestamps();
            $table->unsignedInteger('cig_id');
            $table->string('status');
            $table->dateTime('cig_time_modified');
            $table->string('type');
            $table->string('name');
            $table->decimal('position_x');
            $table->decimal('position_y');
            $table->decimal('position_z');
            $table->string('info_url')->nullable();

            $table->decimal('aggregated_size');
            $table->decimal('aggregated_population');
            $table->decimal('aggregated_economy');
            $table->unsignedInteger('aggregated_danger');

            $table->unsignedInteger('affiliation_id')->nullable();

            $table->foreign('affiliation_id')->references('id')->on('affiliation')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // also remove old starsystems table
        Schema::dropIfExists('starsystems');
        Schema::dropIfExists('starsystem');
    }
}
