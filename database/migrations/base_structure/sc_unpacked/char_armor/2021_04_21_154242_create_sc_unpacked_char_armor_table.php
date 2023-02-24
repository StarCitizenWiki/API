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
            $table->string('damage_reduction')->nullable();
            $table->double('temp_resistance_min')->default(0);
            $table->double('temp_resistance_max')->default(0);
            $table->string('version');
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
