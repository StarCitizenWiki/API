<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_mission_data_mission_chain', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('linked_mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->string('chain_type');
            $table->unsignedInteger('group_index')->default(0);
            $table->uuid('tag_uuid')->nullable();
            $table->string('tag_name')->nullable();
            $table->timestamps();

            $table->index(['mission_data_id', 'chain_type']);
            $table->index(['linked_mission_data_id', 'chain_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_mission_data_mission_chain');
    }
};
