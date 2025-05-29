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
        Schema::table('sc_vehicles', static function (Blueprint $table) {
            // This ideally belongs to the shield controller
            $table->string('shield_face_type')->nullable()->after('expedite_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_vehicles', static function (Blueprint $table) {
            $table->dropColumn('shield_face_type');
        });
    }
};
