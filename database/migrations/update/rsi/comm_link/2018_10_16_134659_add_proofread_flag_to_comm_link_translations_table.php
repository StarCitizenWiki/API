<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProofreadFlagToCommLinkTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'comm_link_translations',
            function (Blueprint $table) {
                $table->boolean('proofread')->after('comm_link_id')->default(false);
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
        Schema::table(
            'comm_link_translations',
            function (Blueprint $table) {
                $table->dropColumn('proofread');
            }
        );
    }
}
