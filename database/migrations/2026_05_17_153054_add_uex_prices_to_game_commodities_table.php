<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table) {
            $table->jsonb('uex_prices')->nullable()->after('images');
        });
    }

    public function down(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table) {
            $table->dropColumn('uex_prices');
        });
    }
};
