<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapon_translations', function (Blueprint $table) {
            $table->id();
            $table->char('locale_code', 5);
            $table->unsignedBigInteger('weapon_id');
            $table->text('translation');
            $table->timestamps();

            $table->unique(['locale_code', 'weapon_id'], 'weapon_translations_primary');
            $table->foreign('locale_code', 'weapon_translations_locale')->references('locale_code')->on('languages');
            $table->foreign('weapon_id', 'weapon_weapon_id_foreign')
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
        Schema::dropIfExists('star_citizen_unpacked_personal_weapon_translations');
    }
}
