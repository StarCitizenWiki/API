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
        Schema::create('sc_vehicle_parts', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name');
            $table->unsignedDouble('damage_max');

            $table->timestamps();

            $table->foreign('vehicle_id', 'fk_sc_v_parts_vehicle_id')
                ->references('id')
                ->on('sc_vehicles')
                ->onDelete('cascade');

            $table->foreign('parent_id', 'fk_sc_v_parts_parent_id')
                ->references('id')
                ->on('sc_vehicle_parts')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_vehicle_parts');
    }
};
