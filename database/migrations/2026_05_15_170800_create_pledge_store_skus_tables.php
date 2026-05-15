<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pledge_store_skus', static function (Blueprint $table): void {
            $table->id();

            $table->unsignedBigInteger('cig_id')->unique();
            $table->string('slug')->nullable();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('subtitle')->nullable();
            $table->string('url')->nullable();

            $table->unsignedInteger('product_id')->nullable();
            $table->string('public_type_code')->nullable();
            $table->string('parent_product_slug')->nullable();
            $table->string('parent_product_name')->nullable();

            $table->boolean('is_warbond')->default(false);
            $table->boolean('is_package')->default(false);
            $table->boolean('is_vip')->default(false);
            $table->boolean('customizable')->default(false);

            $table->unsignedInteger('native_price');
            $table->unsignedInteger('native_discounted')->nullable();
            $table->string('discount_description')->nullable();

            $table->boolean('stock_available')->default(false);
            $table->boolean('stock_unlimited')->default(false);
            $table->boolean('stock_back_order')->default(false);
            $table->unsignedInteger('stock_qty')->default(0);
            $table->unsignedInteger('stock_back_order_qty')->default(0);
            $table->string('stock_level')->nullable();

            $table->json('tags')->nullable();
            $table->json('ships')->nullable();

            $table->timestamps();
        });

        Schema::create('pledge_store_sku_history', static function (Blueprint $table): void {
            $table->id();

            $table->foreignId('pledge_store_sku_id')->constrained()->cascadeOnDelete();

            $table->unsignedInteger('native_price');
            $table->unsignedInteger('native_discounted')->nullable();
            $table->string('discount_description')->nullable();

            $table->boolean('stock_available')->default(false);
            $table->string('stock_level')->nullable();
            $table->unsignedInteger('stock_qty')->default(0);

            $table->json('tags')->nullable();

            $table->timestamp('created_at');
        });

        Schema::create('pledge_store_products', static function (Blueprint $table): void {
            $table->id();

            $table->unsignedInteger('cig_id')->unique();
            $table->string('title');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pledge_store_sku_history');
        Schema::dropIfExists('pledge_store_skus');
        Schema::dropIfExists('pledge_store_products');
    }
};
