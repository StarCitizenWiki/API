<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_resource_types', static function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->unique()->index()->after('key');
            $table->string('refined_version_name')->nullable()->after('refined_version_uuid');
            $table->string('tier')->nullable()->after('has_default_cargo_containers');
        });

        Schema::table('game_starmap_location_data', static function (Blueprint $table) {
            $table->string('slug', 255)->nullable()->after('name');
            $table->unique(['slug', 'game_version_id']);
            $table->index(['slug', 'game_version_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_resource_types', static function (Blueprint $table) {
            $table->dropColumn('slug');
            $table->dropColumn('refined_version_name');
            $table->dropColumn('tier');
        });

        Schema::table('game_starmap_location_data', static function (Blueprint $table) {
            $table->dropIndex(['slug', 'game_version_id']);
            $table->dropUnique(['slug', 'game_version_id']);
            $table->dropColumn('slug');
        });
    }
};
