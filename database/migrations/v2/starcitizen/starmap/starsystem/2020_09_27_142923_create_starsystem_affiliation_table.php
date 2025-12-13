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
        Schema::create(
            'starsystem_affiliation',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('starsystem_id');
                $table->unsignedBigInteger('affiliation_id');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starsystem_affiliation');
    }
};
