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
            $table->string('name');
            $table->string('parent')->nullable();
            $table->unsignedDouble('damage_max');

            $table->timestamps();

            $table->foreign('vehicle_id', 'fk_sc_v_parts_vehicle_id')
                ->references('id')
                ->on('sc_vehicles')
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
