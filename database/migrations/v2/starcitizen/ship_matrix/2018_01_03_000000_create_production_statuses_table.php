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
            'shipmatrix_production_statuses',
            static function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->json('translation')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('shipmatrix_production_statuses');
    }
};
