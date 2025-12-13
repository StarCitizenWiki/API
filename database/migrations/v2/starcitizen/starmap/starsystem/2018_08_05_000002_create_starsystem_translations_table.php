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
            'starsystem_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->string('locale_code', 25);
                $table->unsignedBigInteger('starsystem_id');
                $table->text('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'starsystem_id'], 'starsystem_translation_primary');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('starsystem_translations');
    }
};
