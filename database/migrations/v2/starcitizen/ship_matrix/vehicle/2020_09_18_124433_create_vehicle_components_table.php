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
            'shipmatrix_vehicle_components',
            static function (Blueprint $table) {
                $table->id();
                $table->string('type');
                $table->string('name');
                $table->string('component_class');
                $table->string('component_size', 3)->nullable();
                $table->string('category', 3)->nullable();
                $table->string('manufacturer')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('shipmatrix_vehicle_components');
    }
};
