<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedItemTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_item_translations', function (Blueprint $table) {
            $table->id();
            $table->char('locale_code', 5);
            $table->string('item_uuid');
            $table->text('translation');
            $table->timestamps();

            $table->unique(['locale_code', 'item_uuid'], 'sc_unpacked_item_translations_primary');
            $table->foreign('locale_code', 'sc_unpacked_item_translations_locale')
                ->references('locale_code')
                ->on('languages');
            $table->foreign('item_uuid', 'item_id_trans_foreign')
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
        Schema::dropIfExists('star_citizen_unpacked_item_translations');
    }
}
