<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_vehicle_data', function (Blueprint $table) {
            $table->json('uex_purchase_prices')->nullable()->after('data');
            $table->json('uex_rental_prices')->nullable()->after('uex_purchase_prices');
        });
    }

    public function down(): void
    {
        Schema::table('game_vehicle_data', function (Blueprint $table) {
            $table->dropColumn(['uex_purchase_prices', 'uex_rental_prices']);
        });
    }
};
