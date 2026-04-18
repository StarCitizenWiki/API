<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('blueprint_data_id')->constrained('game_blueprint_data')->cascadeOnDelete();
            $table->float('chance')->nullable();
            $table->uuid('pool_uuid')->nullable();
            $table->uuid('item_uuid')->nullable();
            $table->string('item_name')->nullable();
            $table->timestamps();

            $table->unique(['mission_data_id', 'blueprint_data_id', 'item_uuid']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_mission_data_blueprint');
    }
};
