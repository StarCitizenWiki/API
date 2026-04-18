<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_mission_data_hauling_order', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->string('item_kind');
            $table->uuid('item_uuid')->nullable();
            $table->string('item_name')->nullable();
            $table->unsignedInteger('min_amount')->nullable();
            $table->unsignedInteger('max_amount')->nullable();
            $table->unsignedInteger('max_container_size')->nullable();
            $table->float('min_scu')->nullable();
            $table->float('max_scu')->nullable();
            $table->timestamps();

            $table->index(['mission_data_id', 'item_kind']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_mission_data_hauling_order');
    }
};
