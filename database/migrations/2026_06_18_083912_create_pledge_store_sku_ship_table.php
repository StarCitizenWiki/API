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
        Schema::create('pledge_store_sku_ship', function (Blueprint $table): void {
            $table->foreignId('pledge_store_sku_id')
                ->constrained('pledge_store_skus')
                ->cascadeOnDelete();

            $table->foreignId('shipmatrix_vehicle_id')
                ->constrained('shipmatrix_vehicles')
                ->cascadeOnDelete();

            $table->primary(['pledge_store_sku_id', 'shipmatrix_vehicle_id']);

            $table->timestamp('created_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pledge_store_sku_ship');
    }
};
