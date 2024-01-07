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
        Schema::create('vehicle_skus', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('vehicle_id');
            $table->string('title');
            $table->unsignedInteger('price');
            $table->boolean('available');
            $table->unsignedBigInteger('cig_id');
            $table->timestamps();

            $table->foreign('vehicle_id', 'fk_vehicle_s_vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_skus');
    }
};
