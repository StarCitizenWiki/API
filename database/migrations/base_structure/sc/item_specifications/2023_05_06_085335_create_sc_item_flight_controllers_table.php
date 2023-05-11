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
        Schema::create('sc_item_flight_controllers', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->unsignedDouble('scm_speed')->nullable();
            $table->unsignedDouble('max_speed')->nullable();
            $table->unsignedDouble('pitch')->nullable();
            $table->unsignedDouble('yaw')->nullable();
            $table->unsignedDouble('roll')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_v_f_con_item_uuid')
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
        Schema::dropIfExists('sc_item_flight_controllers');
    }
};
