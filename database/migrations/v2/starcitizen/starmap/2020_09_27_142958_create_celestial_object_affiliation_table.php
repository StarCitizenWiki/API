<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'starmap_celestial_object_affiliation',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('celestial_object_id');
                $table->unsignedBigInteger('affiliation_id');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('starmap_celestial_object_affiliation');
    }
};
