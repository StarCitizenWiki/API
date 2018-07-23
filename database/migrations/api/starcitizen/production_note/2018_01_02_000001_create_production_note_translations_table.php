<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionNoteTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'production_note_translations',
            function (Blueprint $table) {
                $table->char('locale_code', 5);
                $table->unsignedInteger('production_note_id');
                $table->string('translation');
                $table->timestamps();

                $table->primary(['locale_code', 'production_note_id'], 'production_note_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');
                $table->foreign('production_note_id')->references('id')->on('production_notes')->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('production_note_translations');
    }
}
