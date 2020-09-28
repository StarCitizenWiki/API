<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCelestialObjectAffiliationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'celestial_object_affiliation',
            function (Blueprint $table) {
                //$table->id();

                $table->unsignedBigInteger('celestial_object_id');
                $table->unsignedBigInteger('affiliation_id');

                $table->foreign('celestial_object_id')
                    ->references('id')
                    ->on('celestial_objects')
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
        Schema::dropIfExists('celestial_object_affiliation');
    }
}
