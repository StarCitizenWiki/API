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
            $this->info('Downloading and importing all missing Comm-Links.');
            Artisan::call('comm-links:schedule');
        }
        $this->bar->advance();

        if (!$this->option('skipGalactapedia')) {
            $this->info('Downloading and importing all Galactapedia articles.');
            Artisan::call('galactapedia:import-categories');
            Artisan::call('galactapedia:import-articles');
            Artisan::call('galactapedia:import-properties');
        }
        $this->bar->advance();

        Artisan::call('ship-matrix:download --import');
        $this->bar->advance();

        if (!$this->option('skipStarmap')) {
            $this->info('Downloading and importing starmap.');
            Artisan::call('starmap:download --import');
        }
        $this->bar->advance();

        if (!$this->option('skipScUnpacked')) {
            $this->info('Importing all Star Citizen Items.');
            Artisan::call('sc:import-items');
        }
        $this->bar->finish();

        return Command::SUCCESS;
    }
}
