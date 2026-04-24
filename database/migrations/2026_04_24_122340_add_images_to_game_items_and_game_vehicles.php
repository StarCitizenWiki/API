<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_items', function (Blueprint $table): void {
            $table->jsonb('images')->nullable();
        });

        Schema::table('game_vehicles', function (Blueprint $table): void {
            $table->jsonb('images')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('game_items', function (Blueprint $table): void {
            $table->dropColumn('images');
        });

        Schema::table('game_vehicles', function (Blueprint $table): void {
            $table->dropColumn('images');
        });
    }
};
