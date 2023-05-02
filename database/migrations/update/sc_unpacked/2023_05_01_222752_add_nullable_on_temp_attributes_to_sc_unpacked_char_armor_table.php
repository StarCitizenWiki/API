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
        Schema::table('star_citizen_unpacked_char_armor', function (Blueprint $table) {
            $table->double('temp_resistance_min')->nullable()->change();
            $table->double('temp_resistance_max')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sc_unpacked_char_armor', function (Blueprint $table) {
            $table->double('temp_resistance_min')->default(0)->change();
            $table->double('temp_resistance_max')->default(0)->change();
        });
    }
};
