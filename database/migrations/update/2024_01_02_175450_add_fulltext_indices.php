<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('model_changelogs', static function (Blueprint $table) {
            $table->index('user_id');
        });

        Schema::table('galactapedia_article_translations', static function (Blueprint $table) {
            $table->mediumText('translation')->change();
        });

        Schema::table('comm_link_translations', static function (Blueprint $table) {
            $table->mediumText('translation')->change();
        });

        if (config('database.default') === 'mysql') {
            Schema::table('galactapedia_article_translations', static function (Blueprint $table) {
                $table->fullText('translation');
            });

            Schema::table('comm_link_translations', static function (Blueprint $table) {
                $table->fullText('translation');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('model_changelogs', static function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });

        if (config('database.default') === 'mysql') {
            Schema::table('galactapedia_article_translations', static function (Blueprint $table) {
                $table->dropFullText(['translation']);
            });

            Schema::table('comm_link_translations', static function (Blueprint $table) {
                $table->dropFullText(['translation']);
            });

            DB::statement('ALTER TABLE galactapedia_article_translations MODIFY translation LONGBLOB;');
            DB::statement('ALTER TABLE comm_link_translations MODIFY translation LONGBLOB;');
        } else {
            DB::statement('ALTER TABLE galactapedia_article_translations MODIFY translation BLOB;');
            DB::statement('ALTER TABLE comm_link_translations MODIFY translation BLOB;');
        }
    }
};
