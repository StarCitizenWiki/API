<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipmatrix_vehicle_loaners', static function (Blueprint $table) {
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('loaner_id');
            $table->string('version');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipmatrix_vehicle_loaners');
    }
};
