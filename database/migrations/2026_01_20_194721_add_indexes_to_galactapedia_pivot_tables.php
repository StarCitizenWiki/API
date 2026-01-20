<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('galactapedia_article_categories', function (Blueprint $table) {
            $table->index('article_id', 'galactapedia_article_categories_article_idx');
            $table->index('category_id', 'galactapedia_article_categories_category_idx');
        });

        Schema::table('galactapedia_article_tags', function (Blueprint $table) {
            $table->index('article_id', 'galactapedia_article_tags_article_idx');
            $table->index('tag_id', 'galactapedia_article_tags_tag_idx');
        });

        Schema::table('galactapedia_article_templates', function (Blueprint $table) {
            $table->index('article_id', 'galactapedia_article_templates_article_idx');
            $table->index('template_id', 'galactapedia_article_templates_template_idx');
        });

        Schema::table('galactapedia_article_relates', function (Blueprint $table) {
            $table->index('article_id', 'galactapedia_article_relates_article_idx');
            $table->index('related_article_id', 'galactapedia_article_relates_related_idx');
        });

        Schema::table('galactapedia_article_properties', function (Blueprint $table) {
            $table->index('article_id', 'galactapedia_article_properties_article_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galactapedia_article_categories', function (Blueprint $table) {
            $table->dropIndex('galactapedia_article_categories_article_idx');
            $table->dropIndex('galactapedia_article_categories_category_idx');
        });

        Schema::table('galactapedia_article_tags', function (Blueprint $table) {
            $table->dropIndex('galactapedia_article_tags_article_idx');
            $table->dropIndex('galactapedia_article_tags_tag_idx');
        });

        Schema::table('galactapedia_article_templates', function (Blueprint $table) {
            $table->dropIndex('galactapedia_article_templates_article_idx');
            $table->dropIndex('galactapedia_article_templates_template_idx');
        });

        Schema::table('galactapedia_article_relates', function (Blueprint $table) {
            $table->dropIndex('galactapedia_article_relates_article_idx');
            $table->dropIndex('galactapedia_article_relates_related_idx');
        });

        Schema::table('galactapedia_article_properties', function (Blueprint $table) {
            $table->dropIndex('galactapedia_article_properties_article_idx');
        });
    }
};
