<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNewTranscriptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::rename('transcripts', 'relay_transcripts');

        Schema::create('transcripts', function (Blueprint $table) {
            $table->id();
            $table->string('title');

            $table->string('youtube_id')->unique();
            $table->string('playlist_name')->nullable();
            $table->date('upload_date');
            $table->unsignedBigInteger('runtime');
            $table->string('thumbnail')->nullable();
            $table->text('youtube_description');
            $table->string('filename');
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
        Schema::dropIfExists('transcripts');
        Schema::rename('relay_transcripts', 'transcripts');
    }
}
