<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommLinkTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comm_link_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('comm_link_id');
                //$table->binary('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'comm_link_id'], 'comm_link_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');
                $table->foreign('comm_link_id')->references('id')->on('comm_links')->onDelete('cascade');
            }
        );

        DB::statement("ALTER TABLE comm_link_translations ADD COLUMN translation LONGBLOB AFTER comm_link_id");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('comm_link_translations');
    }
}
