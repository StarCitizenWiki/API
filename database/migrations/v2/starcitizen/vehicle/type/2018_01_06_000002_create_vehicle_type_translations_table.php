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
            'vehicle_type_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->string('locale_code', 25);
                $table->unsignedBigInteger('type_id');
                $table->string('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'type_id'], 'vehicle_type_translations_primary');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_type_translations');
    }
};
