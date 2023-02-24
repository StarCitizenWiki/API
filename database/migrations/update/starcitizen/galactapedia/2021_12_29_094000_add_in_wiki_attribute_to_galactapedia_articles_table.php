<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInWikiAttributeToGalactapediaArticlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('galactapedia_articles', function (Blueprint $table) {

            $table->boolean('in_wiki')
                ->default(false)
                ->after('disabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('galactapedia_articles', function (Blueprint $table) {
            $table->dropColumn('in_wiki');
        });
    }
}
