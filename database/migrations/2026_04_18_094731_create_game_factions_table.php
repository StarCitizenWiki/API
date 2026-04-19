<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_factions', static function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('default_reaction');
            $table->string('faction_type');
            $table->boolean('able_to_arrest')->default(false);
            $table->boolean('polices_lawful_trespass')->default(false);
            $table->boolean('polices_criminality')->default(false);
            $table->boolean('no_legal_rights')->default(false);
            $table->boolean('has_reputation')->default(false)->index();
            $table->string('headquarters')->nullable();
            $table->string('founded')->nullable();
            $table->string('leadership')->nullable();
            $table->string('area')->nullable();
            $table->string('focus')->nullable();
            $table->boolean('lawful')->nullable();
            $table->string('sort_order_scope')->nullable();
            $table->boolean('is_npc')->default(false);
            $table->boolean('hide_in_delphi_app')->default(false);
            $table->timestamps();
        });

        Schema::create('game_faction_scopes', static function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('scope_name');
            $table->string('display_name');
            $table->unsignedInteger('reputation_ceiling');
            $table->integer('initial_reputation')->default(0);
            $table->timestamps();
        });

        Schema::create('game_faction_standings', static function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('faction_scope_id')->constrained('game_faction_scopes')->cascadeOnDelete();
            $table->string('name');
            $table->string('display_name')->nullable();
            $table->integer('min_reputation');
            $table->integer('drift_reputation')->default(0);
            $table->unsignedInteger('drift_time_hours')->default(0);
            $table->boolean('gated')->default(false);
            $table->timestamps();

            $table->index('faction_scope_id');
        });

        Schema::create('game_faction_reputation_refs', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('faction_id')->unique()->constrained('game_factions')->cascadeOnDelete();
            $table->foreignId('faction_scope_id')->nullable()->constrained('game_faction_scopes')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_faction_reputation_refs');
        Schema::dropIfExists('game_faction_standings');
        Schema::dropIfExists('game_faction_scopes');
        Schema::dropIfExists('game_factions');
    }
};
