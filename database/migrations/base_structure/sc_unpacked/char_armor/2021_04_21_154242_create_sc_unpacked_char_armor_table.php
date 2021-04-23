<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedCharArmorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_char_armor', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('armor_type')->nullable();
            $table->string('carrying_capacity')->nullable();
            $table->double('temp_resistance_min')->default(0);
            $table->double('temp_resistance_max')->default(0);
            $table->double('resistance_physical_multiplier')->default(0);
            $table->double('resistance_physical_threshold')->default(0);
            $table->double('resistance_energy_multiplier')->default(0);
            $table->double('resistance_energy_threshold')->default(0);
            $table->double('resistance_distortion_multiplier')->default(0);
            $table->double('resistance_distortion_threshold')->default(0);
            $table->double('resistance_thermal_multiplier')->default(0);
            $table->double('resistance_thermal_threshold')->default(0);
            $table->double('resistance_biochemical_multiplier')->default(0);
            $table->double('resistance_biochemical_threshold')->default(0);
            $table->double('resistance_stun_multiplier')->default(0);
            $table->double('resistance_stun_threshold')->default(0);
            $table->timestamps();

            $table->foreign('uuid', 'armor_uuid_item')
                ->references('uuid')
                ->on('star_citizen_unpacked_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_char_armor');
    }
}
