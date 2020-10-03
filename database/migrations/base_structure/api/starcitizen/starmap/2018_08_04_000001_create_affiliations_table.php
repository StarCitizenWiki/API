<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAffiliationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'affiliations',
            function (Blueprint $table) {
                $table->id('id');
                $table->unsignedBigInteger('cig_id');

                $table->string('name');
                $table->string('code');
                $table->string('color');
                $table->unsignedBigInteger('membership_id')->nullable();

                $table->unique('name');
                $table->unique('code');
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
        Schema::dropIfExists('affiliations');
    }
}
