<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedFoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_foods', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->unsignedInteger('nutritional_density_rating')->nullable();
            $table->unsignedInteger('hydration_efficacy_index')->nullable();
            $table->string('container_type')->nullable();
            $table->boolean('one_shot_consume')->nullable();
            $table->boolean('can_be_reclosed')->nullable();
            $table->boolean('discard_when_consumed')->nullable();
            $table->unsignedInteger('occupancy_volume')->nullable();
            $table->string('version');
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
        Schema::dropIfExists('star_citizen_unpacked_foods');
    }
}
