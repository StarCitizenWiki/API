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
        Schema::create('game_missions', static function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->timestamps();
        });

        Schema::create('game_mission_data', static function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('mission_id');
            $table->unsignedBigInteger('game_version_id')->index();
            $table->foreign('game_version_id')->references('id')->on('game_versions')->cascadeOnDelete();
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
            $table->boolean('has_combat')->default(false);
            $table->boolean('has_defend_objective')->default(false);
            $table->unsignedInteger('enemy_count_min')->nullable();
            $table->unsignedInteger('enemy_count_max')->nullable();
            $table->string('reward_scope')->nullable();
            $table->float('blueprint_drop_chance')->nullable();
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
            $table->index(['game_version_id', 'has_combat']);
            $table->index(['game_version_id', 'has_defend_objective']);
            $table->index(['game_version_id', 'reward_scope']);
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(
                'CREATE INDEX IF NOT EXISTS game_mission_data_star_systems_gin_index ON game_mission_data USING GIN (star_systems)'
            );
        }

        Schema::create('game_mission_data_starmap_location', static function (Blueprint $table): void {
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('starmap_location_data_id')->constrained('game_starmap_location_data')->cascadeOnDelete();
            $table->string('purpose')->nullable();

            $table->primary(['mission_data_id', 'starmap_location_data_id']);
        });

        Schema::create('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('blueprint_data_id')->constrained('game_blueprint_data')->cascadeOnDelete();
            $table->uuid('pool_uuid')->nullable();
            $table->foreignId('item_data_id')->nullable()->constrained('game_item_data')->nullOnDelete();

            $table->primary(['mission_data_id', 'blueprint_data_id', 'item_data_id']);
        });

        Schema::create('game_mission_data_prerequisite_groups', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->unsignedInteger('group_index')->default(0);
            $table->unsignedInteger('required_count')->nullable();
            $table->timestamps();

            $table->index('mission_data_id');
        });

        Schema::create('game_mission_data_prerequisite_group_mission', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prerequisite_group_id')->constrained('game_mission_data_prerequisite_groups', 'id', 'mdpgm_pg_fk')->cascadeOnDelete();
            $table->foreignId('linked_mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->timestamps();

            $table->index('prerequisite_group_id');
            $table->index('linked_mission_data_id');
        });

        Schema::create('game_mission_data_prerequisite_group_tag', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('prerequisite_group_id')->constrained('game_mission_data_prerequisite_groups', 'id', 'mdpgt_pg_fk')->cascadeOnDelete();
            $table->string('type');
            $table->uuid('tag_uuid')->nullable();
            $table->string('tag_name')->nullable();
            $table->timestamps();

            $table->index('prerequisite_group_id');
        });

        Schema::create('game_mission_data_unlock_groups', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->unsignedInteger('group_index')->default(0);
            $table->uuid('tag_uuid')->nullable();
            $table->string('tag_name')->nullable();
            $table->timestamps();

            $table->index('mission_data_id');
        });

        Schema::create('game_mission_data_unlock_group_mission', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('unlock_group_id')->constrained('game_mission_data_unlock_groups', 'id', 'mdugm_ug_fk')->cascadeOnDelete();
            $table->foreignId('linked_mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->timestamps();

            $table->index('unlock_group_id');
            $table->index('linked_mission_data_id');
        });

        Schema::create('game_mission_data_commodity', static function (Blueprint $table): void {
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('commodity_id')->constrained('game_commodities')->cascadeOnDelete();

            $table->primary(['mission_data_id', 'commodity_id']);
        });

        Schema::create('game_mission_data_item', static function (Blueprint $table): void {
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('item_data_id')->constrained('game_item_data')->cascadeOnDelete();

            $table->primary(['mission_data_id', 'item_data_id']);
        });

        Schema::create('game_mission_data_reward_item', static function (Blueprint $table): void {
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('item_data_id')->constrained('game_item_data')->cascadeOnDelete();
            $table->unsignedInteger('amount')->nullable();
            $table->boolean('send_to_home')->nullable();

            $table->primary(['mission_data_id', 'item_data_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_mission_data_reward_item');
        Schema::dropIfExists('game_mission_data_item');
        Schema::dropIfExists('game_mission_data_commodity');
        Schema::dropIfExists('game_mission_data_unlock_group_mission');
        Schema::dropIfExists('game_mission_data_unlock_groups');
        Schema::dropIfExists('game_mission_data_prerequisite_group_tag');
        Schema::dropIfExists('game_mission_data_prerequisite_group_mission');
        Schema::dropIfExists('game_mission_data_prerequisite_groups');
        Schema::dropIfExists('game_mission_data_blueprint');
        Schema::dropIfExists('game_mission_data_starmap_location');

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS game_mission_data_star_systems_gin_index');
        }

        Schema::dropIfExists('game_mission_data');
        Schema::dropIfExists('game_missions');
    }
};
