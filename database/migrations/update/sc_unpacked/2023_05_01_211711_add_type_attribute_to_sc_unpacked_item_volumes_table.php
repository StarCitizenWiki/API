<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {

        Schema::table('star_citizen_unpacked_item_volumes', static function (Blueprint $table) {
            $table->boolean('override')->nullable()->after('volume');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('star_citizen_unpacked_item_volumes', static function (Blueprint $table) {
            $table->dropColumn('override');
        });
    }
};
