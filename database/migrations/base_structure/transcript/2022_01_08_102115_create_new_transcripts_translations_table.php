<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewTranscriptsTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('transcript_translations', 'relay_transcript_translations');

        Schema::create(
            'transcript_translations',
            static function (Blueprint $table) {
                $table->id();
                $table->char('locale_code', 5);
                $table->unsignedBigInteger('transcript_id');
                $table->timestamps();

                $table->unique(['locale_code', 'transcript_id'], 'new_transcript_translations_primary');
                $table->foreign('locale_code', 'new_transcript_locale_code')
                    ->references('locale_code')
                    ->on('languages');

                $table->foreign('transcript_id', 'new_transcript_transcript_id')
                    ->references('id')
                    ->on('transcripts')
                    ->onDelete('cascade');
            }
        );

        if (config('database.connection') === 'mysql') {
            DB::statement('ALTER TABLE transcript_translations ADD COLUMN translation LONGBLOB AFTER transcript_id');
        } else {
            DB::statement('ALTER TABLE transcript_translations ADD COLUMN translation BLOB');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transcript_translations');
        Schema::rename('relay_transcript_translations', 'transcript_translations');
    }
}
