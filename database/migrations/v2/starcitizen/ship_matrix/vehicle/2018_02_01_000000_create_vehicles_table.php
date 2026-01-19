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
            'shipmatrix_vehicles',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cig_id')->unique();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->unsignedBigInteger('manufacturer_id');
                $table->unsignedBigInteger('production_status_id');
                $table->unsignedBigInteger('production_note_id');
                $table->unsignedBigInteger('size_id');
                $table->unsignedBigInteger('type_id');
                $table->decimal('length')->nullable();
                $table->decimal('beam')->nullable();
                $table->decimal('height')->nullable();
                $table->unsignedBigInteger('mass')->nullable();
                $table->decimal('cargo_capacity')->nullable();
                $table->unsignedInteger('min_crew')->nullable();
                $table->unsignedInteger('max_crew')->nullable();
                $table->unsignedInteger('scm_speed')->nullable();
                $table->unsignedInteger('afterburner_speed')->nullable();
                $table->decimal('pitch_max')->nullable();
                $table->decimal('yaw_max')->nullable();
                $table->decimal('roll_max')->nullable();
                $table->decimal('x_axis_acceleration')->nullable();
                $table->decimal('y_axis_acceleration')->nullable();
                $table->decimal('z_axis_acceleration')->nullable();
                $table->unsignedInteger('chassis_id');
                $table->unsignedInteger('msrp')->nullable();
                $table->string('pledge_url')->nullable();
                $table->json('translation')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('shipmatrix_vehicles');
    }
};
