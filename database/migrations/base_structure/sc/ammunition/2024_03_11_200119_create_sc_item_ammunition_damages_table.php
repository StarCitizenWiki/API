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
        Schema::create('sc_ammunition_damages', static function (Blueprint $table) {
            $table->id();
            $table->uuid('ammunition_uuid');
            $table->string('type');
            $table->string('name');

            $table->unsignedDouble('damage');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_ammunition_damages');
    }
};
