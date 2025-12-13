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
            'vehicle_component',
            static function (Blueprint $table) {
                $table->unsignedBigInteger('vehicle_id');
                $table->unsignedBigInteger('component_id');

                $table->unsignedInteger('mounts')->default(0);
                $table->string('size', 3)->nullable();
                $table->text('details')->nullable();
                $table->unsignedInteger('quantity')->default(1);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_component');
    }
};
