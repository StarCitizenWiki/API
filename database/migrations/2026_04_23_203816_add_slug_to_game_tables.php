<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_missions', static function (Blueprint $table) {
            $table->string('slug')->nullable()->after('uuid')->unique();
        });

        Schema::table('game_items', static function (Blueprint $table) {
            $table->string('slug')->nullable()->after('uuid')->unique();
        });

        Schema::table('game_vehicles', static function (Blueprint $table) {
            $table->string('slug')->nullable()->after('uuid')->unique();
        });

        Schema::table('game_starmap_locations', static function (Blueprint $table) {
            $table->string('slug')->nullable()->after('uuid')->unique();
        });

        Schema::table('game_starmap_location_data', static function (Blueprint $table) {
            $table->dropIndex(['slug', 'game_version_id']);
            $table->dropUnique(['slug', 'game_version_id']);
            $table->dropColumn('slug');
        });

        Schema::table('game_blueprints', static function (Blueprint $table) {
            $table->string('slug')->nullable()->after('uuid')->unique();
        });
    }

    public function down(): void
    {
        Schema::table('game_missions', static function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('game_items', static function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('game_vehicles', static function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });

        Schema::table('game_starmap_locations', static function (Blueprint $table) {
            $table->dropUnique('game_starmap_locations_slug_unique');
            $table->dropColumn('slug');
        });

        Schema::table('game_starmap_location_data', static function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('name');
            $table->unique(['slug', 'game_version_id']);
            $table->index(['slug', 'game_version_id']);
        });

        Schema::table('game_blueprints', static function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn('slug');
        });
    }
};
