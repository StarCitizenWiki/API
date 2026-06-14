<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('game_resource_types', 'game_commodities');

        Schema::table('game_commodities', static function (Blueprint $table): void {
            $table->uuid('quality_distribution_uuid')->nullable()->after('box_sizes_scu');
            $table->uuid('quality_location_override_uuid')->nullable()->after('quality_distribution_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('game_commodities', static function (Blueprint $table): void {
            $table->dropColumn(['quality_distribution_uuid', 'quality_location_override_uuid']);
        });

        Schema::rename('game_commodities', 'game_resource_types');
    }
};
