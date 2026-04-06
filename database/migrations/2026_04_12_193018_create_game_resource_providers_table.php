<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_resource_providers', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('game_version_id')->constrained('game_versions')->cascadeOnDelete();
            $table->string('provider_name')->nullable();
            $table->jsonb('areas')->nullable();
            $table->timestamps();

            $table->index(['game_version_id', 'provider_name']);
        });

        Schema::create('game_resource_provider_starmap', static function (Blueprint $table) {
            $table->foreignId('resource_provider_id')->constrained('game_resource_providers')->cascadeOnDelete();
            $table->foreignId('starmap_location_data_id')->constrained('game_starmap_location_data')->cascadeOnDelete();
            $table->primary(['resource_provider_id', 'starmap_location_data_id']);
        });

        Schema::table('game_resource_locations', static function (Blueprint $table) {
            $table->foreignId('resource_provider_id')->nullable()->after('resource_data_id')->constrained('game_resource_providers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('game_resource_locations', static function (Blueprint $table) {
            $table->dropForeign(['resource_provider_id']);
            $table->dropColumn('resource_provider_id');
        });

        Schema::dropIfExists('game_resource_provider_starmap');
        Schema::dropIfExists('game_resource_providers');
    }
};
