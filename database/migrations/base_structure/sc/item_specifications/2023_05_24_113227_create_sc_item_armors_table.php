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
    public function up(): void
    {
        Schema::create('sc_item_armors', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('signal_infrared')->nullable();
            $table->unsignedDouble('signal_electromagnetic')->nullable();
            $table->unsignedDouble('signal_cross_section')->nullable();
            $table->unsignedDouble('damage_physical')->nullable();
            $table->unsignedDouble('damage_energy')->nullable();
            $table->unsignedDouble('damage_distortion')->nullable();
            $table->unsignedDouble('damage_thermal')->nullable();
            $table->unsignedDouble('damage_biochemical')->nullable();
            $table->unsignedDouble('damage_stun')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_arm_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_armors');
    }
};
