<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_item_mining_laser_modifiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mining_laser_id');

            $table->string('name');
            $table->string('modifier');

            $table->timestamps();

            $table->foreign('mining_laser_id', 'fk_sc_i_m_l_mod_mining_laser_id')
                ->references('id')
                ->on('sc_item_mining_lasers')
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
        Schema::dropIfExists('sc_item_mining_laser_modifiers');
    }
};
