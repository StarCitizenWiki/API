<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', function (Blueprint $table) {
            $table->index('name');
            $table->index('class_name');
            $table->index(['type', 'sub_type']);
            $table->index(['manufacturer_id', 'type']);
        });

        Schema::table('game_manufacturers', function (Blueprint $table) {
            $table->index('name');
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['class_name']);
            $table->dropIndex(['type', 'sub_type']);
            $table->dropIndex(['manufacturer_id', 'type']);
        });

        Schema::table('game_manufacturers', function (Blueprint $table) {
            $table->dropIndex(['name']);
        });
    }
};
