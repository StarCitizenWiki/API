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
        Schema::create('sc_shops', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->string('name_raw');
            $table->string('name')->nullable();
            $table->string('position')->nullable();
            $table->double('profit_margin')->default(0);
            $table->string('version');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_shops');
    }
};
