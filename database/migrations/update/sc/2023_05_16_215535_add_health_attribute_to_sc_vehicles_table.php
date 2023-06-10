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
        Schema::table('sc_vehicles', static function (Blueprint $table) {
            $table->unsignedDouble('health')->nullable()
                ->after('mass');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sc_vehicles', static function (Blueprint $table) {
            $table->dropColumn('health');
        });
    }
};
