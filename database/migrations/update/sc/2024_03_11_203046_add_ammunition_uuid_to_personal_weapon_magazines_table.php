<?php

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
        Schema::table('sc_item_personal_weapon_magazines', static function (Blueprint $table) {
            $table->uuid('ammunition_uuid')->nullable()->after('max_ammo_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sc_item_personal_weapon_magazines', static function (Blueprint $table) {
            $table->dropColumn('ammunition_uuid');
        });
    }
};
