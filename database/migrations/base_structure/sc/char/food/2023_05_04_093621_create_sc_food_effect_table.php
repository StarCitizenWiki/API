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
        Schema::create('sc_food_effect', function (Blueprint $table) {
            $table->unsignedBigInteger('food_id');
            $table->unsignedBigInteger('food_effect_id');

            $table->foreign('food_id', 'fk_sc_f_eff_food_id')
                ->references('id')
                ->on('sc_foods')
                ->onDelete('cascade');

            $table->foreign('food_effect_id', 'fk_sc_f_eff_food_effect_id')
                ->references('id')
                ->on('sc_food_effects')
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
        Schema::dropIfExists('sc_food_effect');
    }
};
