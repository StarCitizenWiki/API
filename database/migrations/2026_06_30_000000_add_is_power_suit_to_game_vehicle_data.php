<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_vehicle_data', static function (Blueprint $table): void {
            $table->boolean('is_power_suit')->default(false)->after('is_spaceship');
            $table->index('is_power_suit');
        });
    }

    public function down(): void
    {
        Schema::table('game_vehicle_data', static function (Blueprint $table): void {
            $table->dropIndex(['is_power_suit']);
            $table->dropColumn('is_power_suit');
        });
    }
};
