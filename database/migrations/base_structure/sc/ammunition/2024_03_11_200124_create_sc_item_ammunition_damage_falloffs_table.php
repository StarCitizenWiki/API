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
        Schema::create('sc_ammunition_damage_falloffs', static function (Blueprint $table) {
            $table->id();
            $table->uuid('ammunition_uuid');
            $table->string('type');
            $table->double('physical')->default(0);
            $table->double('energy')->default(0);
            $table->double('distortion')->default(0);
            $table->double('thermal')->default(0);
            $table->double('biochemical')->default(0);
            $table->double('stun')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_ammunition_damage_falloffs');
    }
};
