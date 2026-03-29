<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('game_blueprint_data', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('blueprint_id');
            $table->unsignedBigInteger('game_version_id')->index();
            $table->string('key');
            $table->uuid('category_uuid');
            $table->uuid('output_item_uuid');
            $table->string('output_name')->nullable();
            $table->string('output_class')->nullable();
            $table->unsignedInteger('craft_time_seconds')->nullable();
            $table->boolean('is_available_by_default')->default(false);
            $table->jsonb('ingredient_resource_type_uuids');
            $table->jsonb('data');
            $table->timestamps();

            $table->unique(['blueprint_id', 'game_version_id']);
            $table->index(['game_version_id', 'output_item_uuid']);
            $table->index(['game_version_id', 'category_uuid']);
            $table->index(['game_version_id', 'output_name']);
            $table->index(['game_version_id', 'output_class']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX IF NOT EXISTS game_blueprint_data_ingredient_resource_type_uuids_gin_index ON game_blueprint_data USING GIN (ingredient_resource_type_uuids)'
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS game_blueprint_data_ingredient_resource_type_uuids_gin_index');
        }

        Schema::dropIfExists('game_blueprint_data');
    }
};
