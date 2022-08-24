<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponMagazineAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapon_magazine_attachments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('attachment_id');
            $table->unsignedInteger('capacity');
            $table->timestamps();

            $table->foreign('attachment_id', 'magazine_attachment_id_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_personal_weapon_attachments')
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
        Schema::dropIfExists('star_citizen_unpacked_personal_weapon_magazine_attachments');
    }
}
