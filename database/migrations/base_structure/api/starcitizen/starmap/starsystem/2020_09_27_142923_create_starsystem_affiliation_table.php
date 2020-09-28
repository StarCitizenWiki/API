<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStarsystemAffiliationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'starsystem_affiliation',
            function (Blueprint $table) {
                //$table->id();

                $table->unsignedBigInteger('starsystem_id');
                $table->unsignedBigInteger('affiliation_id');

                $table->foreign('starsystem_id')
                    ->references('id')
                    ->on('starsystems')
                    ->onDelete('cascade');

                $table->foreign('affiliation_id')
                    ->references('id')
                    ->on('affiliations')
                    ->onDelete('cascade');
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
        Schema::dropIfExists('starsystem_affiliation');
    }
}
