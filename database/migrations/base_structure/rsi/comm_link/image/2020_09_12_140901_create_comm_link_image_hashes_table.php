<?php declare(strict_types=1);
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommLinkImageHashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('comm_link_image_hashes', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('comm_link_image_id');
            $table->string('perceptual_hash', 16);
            $table->unsignedBigInteger('p_hash_1');
            $table->unsignedBigInteger('p_hash_2');

            $table->string('difference_hash', 16);
            $table->unsignedBigInteger('d_hash_1');
            $table->unsignedBigInteger('d_hash_2');

            $table->string('average_hash', 16);
            $table->unsignedBigInteger('a_hash_1');
            $table->unsignedBigInteger('a_hash_2');

            $table->timestamps();

            $table->foreign('comm_link_image_id')->references('id')->on('comm_link_images')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_image_hashes');
    }
}
