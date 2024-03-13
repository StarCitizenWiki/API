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
        Schema::create('sc_ammunition_piercabilities', static function (Blueprint $table) {
            $table->id();
            $table->uuid('ammunition_uuid');
            $table->double('damage_falloff_level_1')->default(0);
            $table->double('damage_falloff_level_2')->default(0);
            $table->double('damage_falloff_level_3')->default(0);
            $table->double('max_penetration_thickness')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_ammunition_piercabilities');
    }
};
