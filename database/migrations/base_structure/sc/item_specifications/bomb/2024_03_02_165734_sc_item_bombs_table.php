<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sc_item_bombs', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->unsignedDouble('arm_time')->nullable();
            $table->unsignedDouble('ignite_time')->nullable();
            $table->unsignedDouble('collision_delay_time')->nullable();
            $table->unsignedDouble('explosion_safety_distance')->nullable();
            $table->unsignedDouble('explosion_radius_min')->nullable();
            $table->unsignedDouble('explosion_radius_max')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_bom_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_bombs');
    }
};
