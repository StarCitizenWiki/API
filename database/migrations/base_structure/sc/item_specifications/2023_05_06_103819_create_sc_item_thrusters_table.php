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
        Schema::create('sc_item_thrusters', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('thrust_capacity');
            $table->unsignedDouble('min_health_thrust_multiplier');
            $table->unsignedDouble('fuel_burn_per_10k_newton');
            $table->string('type');

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_thr_item_uuid')
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
        Schema::dropIfExists('sc_item_thrusters');
    }
};
