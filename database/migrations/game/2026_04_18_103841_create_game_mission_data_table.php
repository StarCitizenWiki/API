<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_mission_data', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mission_id');
            $table->unsignedBigInteger('game_version_id')->index();
            $table->string('debug_name')->nullable();
            $table->string('mission_type')->nullable();
            $table->uuid('mission_type_uuid')->nullable();
            $table->string('mission_giver')->nullable();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('faction_id')->nullable()->constrained('game_factions')->nullOnDelete();
            $table->string('generator_class')->nullable();
            $table->string('entry_type')->nullable();
            $table->string('handler_type')->nullable();
            $table->boolean('illegal')->default(false);
            $table->boolean('shareable')->default(false);
            $table->boolean('once_only')->default(false);
            $table->boolean('available_in_prison')->default(false);
            $table->boolean('not_for_release')->default(false);
            $table->boolean('work_in_progress')->default(false);
            $table->boolean('calculated_reward')->default(false);
            $table->unsignedInteger('rank_index')->nullable();
            $table->unsignedInteger('min_crime_stat')->nullable();
            $table->unsignedInteger('max_crime_stat')->nullable();
            $table->float('time_to_complete_minutes')->nullable();
            $table->unsignedInteger('reward_min')->nullable();
            $table->unsignedInteger('reward_max')->nullable();
            $table->string('reward_currency')->nullable();
            $table->jsonb('star_systems')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();

            $table->unique(['mission_id', 'game_version_id']);
            $table->index(['game_version_id', 'mission_type']);
            $table->index(['game_version_id', 'faction_id']);
            $table->index(['game_version_id', 'entry_type']);
            $table->index(['game_version_id', 'generator_class']);
            $table->index(['game_version_id', 'illegal']);
            $table->index(['game_version_id', 'shareable']);
            $table->index(['game_version_id', 'mission_giver']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX IF NOT EXISTS game_mission_data_star_systems_gin_index ON game_mission_data USING GIN (star_systems)'
            );
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS game_mission_data_star_systems_gin_index');
        }

        Schema::dropIfExists('game_mission_data');
    }
};
