<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ship_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('ship_id');
                $table->text('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'ship_id'], 'ship_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');
                $table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
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
        Schema::dropIfExists('ship_translations');
    }
}
