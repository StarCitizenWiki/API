<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTranscriptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'transcripts',
            static function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('wiki_id')->unique()->nullable();

                $table->string('title')->nullable();
                $table->string('youtube_url')->unique()->nullable();
                $table->unsignedBigInteger('format_id')->default(1);

                $table->string('source_title');
                $table->string('source_url')->unique();
                $table->timestamp('source_published_at');

                $table->timestamp('published_at')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('transcripts');
    }
}
