<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedCharArmorAttachment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_char_armor_attachment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('char_armor_id');
            $table->unsignedBigInteger('char_armor_attachment_id');

            $table->foreign('char_armor_id', 'armor_item_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_char_armor')
                ->onDelete('cascade');

            $table->foreign('char_armor_attachment_id', 'armor_attachment_item_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_char_armor_attachments')
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
        Schema::dropIfExists('star_citizen_unpacked_char_armor_attachment');
    }
}
