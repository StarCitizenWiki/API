<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductionStatusTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'production_status_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('production_status_id');
                $table->string('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'production_status_id'], 'production_status_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');
                $table->foreign('production_status_id')->references('id')->on('production_statuses')->onDelete('cascade');
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
        Schema::dropIfExists('production_status_translations');
    }
}
