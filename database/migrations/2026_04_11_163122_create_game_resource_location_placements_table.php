<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_resource_location_placements', static function (Blueprint $table) {
            $table->foreignId('resource_location_id')->constrained('game_resource_locations')->cascadeOnDelete();
            $table->foreignId('starmap_location_data_id')->constrained('game_starmap_location_data')->cascadeOnDelete();

            $table->primary(['resource_location_id', 'starmap_location_data_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_resource_location_placements');
    }
};
