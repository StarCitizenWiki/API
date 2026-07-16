<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table): void {
            $table->decimal('volatility')->nullable()->after('density_g_per_cc');
            $table->decimal('volatility_health_decay_per_second')->nullable()->after('volatility');
        });
    }

    public function down(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table): void {
            $table->dropColumn(['volatility', 'volatility_health_decay_per_second']);
        });
    }
};
