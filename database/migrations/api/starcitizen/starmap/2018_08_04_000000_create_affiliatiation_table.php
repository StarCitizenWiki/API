<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAffiliatiationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('affiliation', function (Blueprint $table) {
            $table->unsignedInteger('id');
            $table->string('name');
            $table->string('code');
            $table->string('color');
            $table->unsignedInteger('membership_id')->nullable();

            $table->primary('id');
            $table->unique('name');
            $table->unique('code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('affiliation');
    }
}
