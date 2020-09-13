<?php

namespace App\Console\Commands\CommLink\Image;

use App\Jobs\Rsi\CommLink\Image\CreateImageHash;
use App\Models\Rsi\CommLink\Image\Image;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class CreateImageHashes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'comm-links:create-image-hashes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Creates Image hashes for all downloaded Comm-Links';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Creating Image Hashes');

        $query = Image::query()->whereDoesntHave('hash');

        $bar = $this->output->createProgressBar($query->count());

        $query->chunk(
            100,
            function (Collection $images) use ($bar) {
                $images->each(
                    function (Image $image) use ($bar) {
                        dispatch(new CreateImageHash($image));
                        $bar->advance();
                    }
                );
            }
        );

        $bar->finish();

        return 0;
    }
}
