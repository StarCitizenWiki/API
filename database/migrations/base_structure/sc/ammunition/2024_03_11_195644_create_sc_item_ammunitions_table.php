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
        Schema::create('sc_ammunitions', static function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->unsignedInteger('size');
            $table->unsignedDouble('lifetime');
            $table->unsignedDouble('speed');
            $table->unsignedDouble('range');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_ammunitions');
    }
};
