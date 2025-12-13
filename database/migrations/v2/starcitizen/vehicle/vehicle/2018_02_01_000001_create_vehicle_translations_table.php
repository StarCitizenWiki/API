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
            'vehicle_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->string('locale_code', 25);
                $table->unsignedBigInteger('vehicle_id');
                $table->text('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'vehicle_id'], 'vehicle_translations_primary');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_translations');
    }
};
