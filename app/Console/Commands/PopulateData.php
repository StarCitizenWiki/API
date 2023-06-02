<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class PopulateData extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'data:populate {--seed} {--skipCommLinks} {--skipGalactapedia} {--skipScUnpacked} {--skipStarmap}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Imports all available data';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->createProgressBar(6);

        if ($this->option('seed')) {
            Artisan::call('db:seed');
        }
        $this->bar->advance();

        if (!$this->option('skipCommLinks')) {
            Artisan::call('comm-links:schedule');
        }
        $this->bar->advance();

        if (!$this->option('skipGalactapedia')) {
            Artisan::call('galactapedia:import-categories');
            Artisan::call('galactapedia:import-articles');
            Artisan::call('galactapedia:import-properties');
        }
        $this->bar->advance();

        Artisan::call('ship-matrix:download --import');
        $this->bar->advance();

        if (!$this->option('skipStarmap')) {
            Artisan::call('starmap:download --import');
        }
        $this->bar->advance();

        if (!$this->option('skipScUnpacked')) {
            Artisan::call('sc:import-items');
            Artisan::call('sc:import-shops');
        }
        $this->bar->finish();

        return Command::SUCCESS;
    }
}
