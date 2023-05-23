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
        Schema::table('sc_item_heat_data', static function (Blueprint $table) {
            $table->unsignedDouble('ir_temperature_threshold')->nullable()
                ->after('temperature_to_ir');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('sc_item_heat_data', static function (Blueprint $table) {
            $table->dropColumn('ir_temperature_threshold');
        });
    }
};
