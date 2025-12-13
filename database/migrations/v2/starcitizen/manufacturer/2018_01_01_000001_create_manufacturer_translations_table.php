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
            'manufacturer_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->string('locale_code', 25);
                $table->unsignedBigInteger('manufacturer_id');
                $table->string('known_for')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->unique(['locale_code', 'manufacturer_id'], 'manufacturer_translations_primary');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturer_translations');
    }
};
