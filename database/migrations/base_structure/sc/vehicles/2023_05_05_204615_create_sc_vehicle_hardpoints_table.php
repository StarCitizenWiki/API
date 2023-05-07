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
        Schema::create('sc_vehicle_hardpoints', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('parent_hardpoint_id')->nullable();

            $table->string('hardpoint_name');

            $table->uuid('equipped_item_uuid')->nullable();

            $table->unsignedInteger('min_size')->nullable();
            $table->unsignedInteger('max_size')->nullable();

            $table->string('class_name')->nullable();

            $table->index('vehicle_id');
            $table->index('parent_hardpoint_id');

            $table->foreign('vehicle_id', 'fk_sc_v_h_vehicle_id')
                ->references('id')
                ->on('sc_vehicles')
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
        Schema::dropIfExists('sc_vehicle_hardpoints');
    }
};
