<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_starmap_location_data', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('starmap_location_id')
                ->constrained('game_starmap_locations')
                ->cascadeOnDelete();
            $table->foreignId('game_version_id')
                ->constrained('game_versions')
                ->cascadeOnDelete();
            $table->foreignId('parent_data_id')
                ->nullable()
                ->constrained('game_starmap_location_data')
                ->nullOnDelete();
            $table->foreignId('star_data_id')
                ->nullable()
                ->constrained('game_starmap_location_data')
                ->nullOnDelete();
            $table->foreignId('location_hierarchy_entity_tag_id')
                ->nullable()
                ->constrained('game_entity_tags')
                ->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type_name');
            $table->string('system')->nullable();
            $table->double('size')->nullable();
            $table->boolean('is_scannable')->default(false);
            $table->boolean('block_travel')->default(false);
            $table->jsonb('data');
            $table->timestamps();

            $table->unique(['starmap_location_id', 'game_version_id']);
            $table->index('game_version_id');
            $table->index('parent_data_id');
            $table->index('star_data_id');
            $table->index('location_hierarchy_entity_tag_id');
            $table->index(['game_version_id', 'name']);
            $table->index(['game_version_id', 'system']);
            $table->index(['game_version_id', 'type_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_starmap_location_data');
    }
};
