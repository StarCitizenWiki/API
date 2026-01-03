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
        Schema::create('comm_link_image_hashes', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('comm_link_image_id')
                ->constrained('comm_link_images')
                ->cascadeOnDelete();
            if (Schema::getConnection()->getDriverName() === 'sqlite') {
                $table->text('pdq_hash');
            } else {
                $table->addColumn('raw', 'pdq_hash', ['definition' => 'bit(256)']);
            }
            $table->smallInteger('pdq_quality')->nullable();
            $table->timestamps();

            $table->unique('comm_link_image_id');
            $table->index('pdq_hash');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_image_hashes');
    }
};
