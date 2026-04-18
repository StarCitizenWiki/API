<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_mission_data_starmap_location', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('starmap_location_data_id')->constrained('game_starmap_location_data')->cascadeOnDelete();
            $table->string('source');
            $table->string('pool_key')->nullable();
            $table->string('pool_purpose')->nullable();
            $table->timestamps();

            $table->unique(['mission_data_id', 'starmap_location_data_id', 'pool_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_mission_data_starmap_location');
    }
};
