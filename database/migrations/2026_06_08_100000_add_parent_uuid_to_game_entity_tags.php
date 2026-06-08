<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_entity_tags', static function (Blueprint $table) {
            $table->uuid('parent_uuid')->nullable()->after('name');

            $table->foreign('parent_uuid')
                ->references('uuid')
                ->on('game_entity_tags')
                ->nullOnDelete();

            $table->index('parent_uuid');
        });
    }

    public function down(): void
    {
        Schema::table('game_entity_tags', static function (Blueprint $table) {
            $table->dropForeign(['parent_uuid']);
            $table->dropColumn('parent_uuid');
        });
    }
};
