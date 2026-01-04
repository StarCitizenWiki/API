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
            'shipmatrix_manufacturers',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cig_id');
                $table->string('name');
                $table->string('name_short')->unique();
                $table->json('known_for')->nullable();
                $table->json('description')->nullable();
                $table->timestamps();

                $table->unique('cig_id');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('shipmatrix_manufacturers');
    }
};
