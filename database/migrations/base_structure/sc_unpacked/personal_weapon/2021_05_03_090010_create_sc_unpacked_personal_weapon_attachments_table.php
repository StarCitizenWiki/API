<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponAttachmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapon_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('weapon_id');

            $table->string('name');
            $table->string('position');
            $table->unsignedInteger('size');
            $table->unsignedInteger('grade');

            $table->timestamps();

            $table->foreign('weapon_id', 'attachment_weapon_id_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_personal_weapons')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_personal_weapon_attachments');
    }
}
