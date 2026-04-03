<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_starmap_locations', static function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->uuid('system_uuid')->nullable();
            $table->timestamps();

            $table->index('system_uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_starmap_locations');
    }
};
