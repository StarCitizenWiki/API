<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAppearanceNullableInCelestialObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('celestial_objects', function (Blueprint $table) {
            $table->string('appearance')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('celestial_objects', function (Blueprint $table) {
            $table->string('appearance')->nullable(false)->change();
        });
    }
}
