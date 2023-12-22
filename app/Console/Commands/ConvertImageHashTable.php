<?php

namespace App\Console\Commands;

use App\Models\Rsi\CommLink\Image\ImageHash;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertImageHashTable extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:convert-image-hash-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (config('database.default') === 'sqlite') {
            $this->error('Does not work with sqlite.');

            return 1;
        }

        Schema::dropIfExists('comm_link_image_hashes_temp');
        Schema::create('comm_link_image_hashes_temp', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('comm_link_image_id');
            $table->binary('average_hash');
            $table->binary('perceptual_hash');
            $table->binary('difference_hash');
            $table->timestamps();

            $table->foreign('comm_link_image_id')->references('id')->on('comm_link_images')->onDelete('cascade');
            $table->unique('comm_link_image_id');
        });

        $select = ImageHash::query()
            ->select(['comm_link_image_id'])
            ->selectRaw('CAST(CONV(perceptual_hash, 16, 10) AS UNSIGNED INTEGER) as perceptual_hash')
            ->selectRaw('CAST(CONV(average_hash, 16, 10) AS UNSIGNED INTEGER) as average_hash')
            ->selectRaw('CAST(CONV(difference_hash, 16, 10) AS UNSIGNED INTEGER) as difference_hash')
            ->selectRaw('created_at')
            ->selectRaw('updated_at');

        DB::table('comm_link_image_hashes_temp')->insertUsing([
            'comm_link_image_id',
            'perceptual_hash',
            'average_hash',
            'difference_hash',
            'created_at',
            'updated_at',
        ], $select);

        Schema::rename('comm_link_image_hashes', 'comm_link_image_hashes_old');
        Schema::rename('comm_link_image_hashes_temp', 'comm_link_image_hashes');

        return 0;
    }
}
