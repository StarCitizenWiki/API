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
        Schema::table('sc_item_fuel_tanks', static function (Blueprint $table) {
            $table->unsignedDouble('fill_rate')->nullable()->change();
            $table->unsignedDouble('drain_rate')->nullable()->change();
            $table->unsignedDouble('capacity')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_item_fuel_tanks', static function (Blueprint $table) {
            $table->unsignedDouble('fill_rate')->nullable(false)->change();
            $table->unsignedDouble('drain_rate')->nullable(false)->change();
            $table->unsignedDouble('capacity')->nullable(false)->change();
        });
    }
};
