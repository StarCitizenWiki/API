<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapons', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('size')->default(0);
            $table->string('manufacturer')->nullable();
            $table->string('type')->nullable();
            $table->string('class')->nullable();
            $table->unsignedInteger('magazine_size')->default(0);
            $table->string('effective_range')->default(0);
            $table->string('rof')->default(0);
            $table->string('attachment_size_optics')->nullable();
            $table->string('attachment_size_barrel')->nullable();
            $table->string('attachment_size_underbarrel')->nullable();
            $table->unsignedDouble('ammunition_speed')->default(0);
            $table->unsignedDouble('ammunition_range')->default(0);
            $table->unsignedDouble('ammunition_damage')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_personal_weapons');
    }
}
