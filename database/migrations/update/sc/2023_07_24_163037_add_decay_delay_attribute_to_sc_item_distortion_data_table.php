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
        Schema::table('sc_item_distortion_data', static function (Blueprint $table) {
            $table->unsignedDouble('decay_delay')->nullable()
                ->after('decay_rate');
            $table->unsignedDouble('warning_ratio')->nullable()
                ->after('maximum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_item_distortion_data', static function (Blueprint $table) {
            $table->dropColumn('decay_delay');
            $table->dropColumn('warning_ratio');
        });
    }
};
