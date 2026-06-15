<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Migrates mission reward items from the flat pivot format (game_mission_data_reward_item) into a group-based model
 *
 * Legacy flat reward sets migrate into a single group
 * (group_index = 0, weight = null, award_only_to_mission_owner = null).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_mission_reward_groups', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->unsignedInteger('group_index')->default(0);
            $table->float('weight')->nullable();
            $table->boolean('award_only_to_mission_owner')->nullable();
            $table->timestamps();

            $table->index('mission_data_id');
        });

        Schema::create('game_mission_reward_group_item', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('reward_group_id')->constrained('game_mission_reward_groups', 'id', 'gmrgi_rg_fk')->cascadeOnDelete();
            $table->foreignId('item_data_id')->constrained('game_item_data')->cascadeOnDelete();
            $table->unsignedInteger('amount')->nullable();
            $table->boolean('send_to_home')->nullable();
            $table->timestamps();

            $table->index('reward_group_id');
            $table->index('item_data_id');
        });

        // Migrate existing flat reward rows forward into a single group per mission.
        DB::statement(<<<'SQL'
            INSERT INTO game_mission_reward_groups (mission_data_id, group_index, weight, award_only_to_mission_owner, created_at, updated_at)
            SELECT DISTINCT mission_data_id, 0, NULL::float, NULL::boolean, NOW(), NOW()
            FROM game_mission_data_reward_item
            ORDER BY mission_data_id
        SQL);

        DB::table('game_mission_reward_group_item')->insertUsing(
            ['reward_group_id', 'item_data_id', 'amount', 'send_to_home', 'created_at', 'updated_at'],
            static function ($query): void {
                $query->from('game_mission_data_reward_item as ri')
                    ->join('game_mission_reward_groups as rg', 'rg.mission_data_id', '=', 'ri.mission_data_id')
                    ->select('rg.id', 'ri.item_data_id', 'ri.amount', 'ri.send_to_home')
                    ->selectRaw('NOW(), NOW()');
            }
        );

        Schema::dropIfExists('game_mission_data_reward_item');
    }

    public function down(): void
    {
        Schema::create('game_mission_data_reward_item', static function (Blueprint $table): void {
            $table->foreignId('mission_data_id')->constrained('game_mission_data')->cascadeOnDelete();
            $table->foreignId('item_data_id')->constrained('game_item_data')->cascadeOnDelete();
            $table->unsignedInteger('amount')->nullable();
            $table->boolean('send_to_home')->nullable();

            $table->primary(['mission_data_id', 'item_data_id']);
        });

        DB::table('game_mission_data_reward_item')->insertUsing(
            ['mission_data_id', 'item_data_id', 'amount', 'send_to_home'],
            static function ($query): void {
                $query->from('game_mission_reward_group_item as gi')
                    ->join('game_mission_reward_groups as g', 'g.id', '=', 'gi.reward_group_id')
                    ->whereIn('g.mission_data_id', static function ($sub): void {
                        $sub->select('mission_data_id')
                            ->from('game_mission_reward_groups')
                            ->groupBy('mission_data_id')
                            ->havingRaw('COUNT(*) = 1')
                            ->havingRaw('MAX(weight) IS NULL')
                            ->havingRaw('MAX(award_only_to_mission_owner) IS NULL');
                    })
                    ->select('g.mission_data_id', 'gi.item_data_id', 'gi.amount', 'gi.send_to_home');
            }
        );

        Schema::dropIfExists('game_mission_reward_group_item');
        Schema::dropIfExists('game_mission_reward_groups');
    }
};
