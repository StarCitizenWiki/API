<?php

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
    public function up()
    {
        Schema::table('star_citizen_unpacked_personal_weapons', static function (Blueprint $table) {
            $table->unsignedDouble('effective_range')->nullable()->change();
            $table->string('rof')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('star_citizen_unpacked_personal_weapons', static function (Blueprint $table) {
            $table->unsignedDouble('effective_range')->default(0)->change();
            $table->string('rof')->default(0)->change();
        });
    }
};
