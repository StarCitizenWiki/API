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
        Schema::table('sc_items', static function (Blueprint $table) {
            $table->unsignedBigInteger('base_id')->nullable()->after('mass');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_items', static function (Blueprint $table) {
            $table->dropColumn('base_id');
        });
    }
};
