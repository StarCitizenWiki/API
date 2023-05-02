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
        Schema::table('star_citizen_unpacked_ship_weapons', function (Blueprint $table) {
            $table->unsignedDouble('speed')->nullable()->change();
            $table->unsignedDouble('range')->nullable()->change();
            $table->unsignedDouble('size')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('star_citizen_unpacked_ship_weapons', function (Blueprint $table) {
            $table->unsignedDouble('speed')->change();
            $table->unsignedDouble('range')->change();
            $table->unsignedDouble('size')->change();
        });
    }
};
