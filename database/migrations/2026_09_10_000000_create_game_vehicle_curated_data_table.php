<?php

declare(strict_types=1);

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
        Schema::create('game_vehicle_curated_data', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('game_vehicle_id')
                ->unique()
                ->constrained('game_vehicles')
                ->cascadeOnDelete();

            $table->string('trailer_url')->nullable();
            $table->unsignedInteger('original_pledge_price')->nullable();
            $table->unsignedInteger('original_warbond_price')->nullable();
            $table->string('added_in_version')->nullable();
            $table->date('concept_date')->nullable();
            $table->date('sale_date')->nullable();
            $table->date('retire_date')->nullable();
            $table->string('pledge_availability')->nullable();
            $table->jsonb('qa_urls')->nullable();
            $table->string('brochure_url')->nullable();
            $table->jsonb('presentation_urls')->nullable();
            $table->string('whitleys_guide_url')->nullable();
            $table->string('galactapedia_url')->nullable();

            $table->string('wiki_page_title');
            $table->unsignedBigInteger('wiki_revision_id')->nullable();
            $table->jsonb('raw')->nullable();
            $table->timestamp('synced_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_vehicle_curated_data');
    }
};
