<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table) {
            $table->decimal('instability', 12, 4)->nullable()->after('data');
            $table->decimal('resistance', 8, 4)->nullable()->after('instability');
            $table->decimal('density_g_per_cc', 10, 4)->nullable()->after('resistance');
        });
    }

    public function down(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table) {
            $table->dropColumn(['instability', 'resistance', 'density_g_per_cc']);
        });
    }
};
