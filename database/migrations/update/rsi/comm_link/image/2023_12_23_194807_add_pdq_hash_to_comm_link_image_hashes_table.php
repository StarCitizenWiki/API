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
        Schema::table('comm_link_image_hashes', static function (Blueprint $table) {
            $table->binary('pdq_hash1')->nullable()->after('difference_hash');
            $table->binary('pdq_hash2')->nullable()->after('pdq_hash1');
            $table->binary('pdq_hash3')->nullable()->after('pdq_hash2');
            $table->binary('pdq_hash4')->nullable()->after('pdq_hash3');
            $table->smallInteger('pdq_quality')->nullable()->after('pdq_hash4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_link_image_hashes', static function (Blueprint $table) {
            $table->dropColumn('pdq_hash1');
            $table->dropColumn('pdq_hash2');
            $table->dropColumn('pdq_hash3');
            $table->dropColumn('pdq_hash4');
            $table->dropColumn('pdq_quality');
        });
    }
};
