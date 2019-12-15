<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTranscriptTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'transcript_translations',
            static function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('transcript_id');
                $table->boolean('proofread')->default(false);
                $table->longText('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'transcript_id'], 'transcript_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages');
                $table->foreign('transcript_id')->references('id')->on('transcripts')->onDelete('cascade');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transcript_translations');
    }
}
