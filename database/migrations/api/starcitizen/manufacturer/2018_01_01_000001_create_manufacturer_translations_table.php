<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturerTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'manufacturer_translations',
            function (Blueprint $table) {
                $table->char('locale_code', 5);
                $table->unsignedInteger('manufacturer_id');
                $table->string('known_for')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();

                $table->primary(['locale_code', 'manufacturer_id'], 'manufacturer_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');
                $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
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
        Schema::dropIfExists('manufacturer_translations');
    }
}
