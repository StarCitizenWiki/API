<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_starmap_location_data_amenity', static function (Blueprint $table) {
            $table->foreignId('location_data_id')
                ->constrained('game_starmap_location_data')
                ->cascadeOnDelete();
            $table->foreignId('amenity_id')
                ->constrained('game_starmap_amenities')
                ->cascadeOnDelete();

            $table->unique(['location_data_id', 'amenity_id']);
            $table->index('amenity_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_starmap_location_data_amenity');
    }
};
