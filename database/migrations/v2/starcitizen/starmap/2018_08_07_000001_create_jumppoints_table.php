<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'starmap_jumppoints',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cig_id');
                $table->string('direction');
                $table->unsignedBigInteger('entry_id');
                $table->unsignedBigInteger('exit_id');
                $table->string('name')->nullable();
                $table->string('size');

                $table->string('entry_status')->nullable();
                $table->string('exit_status')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('starmap_jumppoints');
    }
};
