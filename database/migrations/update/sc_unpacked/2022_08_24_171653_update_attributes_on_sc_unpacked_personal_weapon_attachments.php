<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateAttributesOnScUnpackedPersonalWeaponAttachments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (\Illuminate\Support\Facades\DB::table('star_citizen_unpacked_personal_weapon_attachments')->exists()) {
            \Illuminate\Support\Facades\DB::table('star_citizen_unpacked_personal_weapon_attachments')->truncate();
        }

        Schema::table('star_citizen_unpacked_personal_weapon_attachments', function (Blueprint $table) {
            $table->dropForeign('attachment_weapon_id_foreign');
            $table->dropColumn('weapon_id');
            $table->dropColumn('name');
        });

        Schema::table('star_citizen_unpacked_personal_weapon_attachments', function (Blueprint $table) {
            $table->string('uuid')->unique();
            $table->string('attachment_name');
            $table->string('type');
            $table->string('version');

            $table->foreign('uuid', 'attachment_uuid_item')
                ->references('uuid')
                ->on('star_citizen_unpacked_items')
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
        Schema::table('star_citizen_unpacked_personal_weapon_attachments', function (Blueprint $table) {
            $table->unsignedBigInteger('weapon_id');
            $table->dropColumn('name');

            $table->foreign('weapon_id', 'attachment_weapon_id_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_personal_weapons')
                ->onDelete('cascade');
        });

        Schema::table('star_citizen_unpacked_personal_weapon_attachments', function (Blueprint $table) {
            $table->dropForeign('attachment_uuid_item');
            $table->dropColumn('uuid');
            $table->dropColumn('type');
            $table->dropColumn('attachment_name');
            $table->dropColumn('version');
        });
    }
}
